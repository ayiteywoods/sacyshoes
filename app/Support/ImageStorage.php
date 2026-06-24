<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use Throwable;

class ImageStorage
{
    public const INPUT_MAX_KILOBYTES = 20480;

    public function store(
        UploadedFile $file,
        string $directory,
        int $targetKilobytes,
        int $maxDimension = 2000,
    ): string {
        $targetBytes = $targetKilobytes * 1024;

        if ($file->getSize() <= $targetBytes && ! $this->exceedsMaxDimension($file, $maxDimension)) {
            return $file->store($directory, 'public');
        }

        if (extension_loaded('imagick')) {
            $path = $this->storeWithImagick($file, $directory, $targetBytes, $maxDimension);

            if ($path !== null) {
                return $path;
            }
        }

        if (extension_loaded('gd')) {
            $path = $this->storeWithGd($file, $directory, $targetBytes, $maxDimension);

            if ($path !== null) {
                return $path;
            }
        }

        return $file->store($directory, 'public');
    }

    private function exceedsMaxDimension(UploadedFile $file, int $maxDimension): bool
    {
        $size = @getimagesize($file->getPathname());

        if (! is_array($size)) {
            return false;
        }

        return max((int) $size[0], (int) $size[1]) > $maxDimension;
    }

    private function storeWithImagick(
        UploadedFile $file,
        string $directory,
        int $targetBytes,
        int $maxDimension,
    ): ?string {
        try {
            $source = new Imagick($file->getPathname());
            $source->autoOrient();
            $source->stripImage();
            $this->fitWithinDimension($source, $maxDimension);

            $bestBlob = null;

            for ($scale = 1.0; $scale >= 0.35; $scale *= 0.85) {
                $attempt = clone $source;

                if ($scale < 1.0) {
                    $width = max(1, (int) round($source->getImageWidth() * $scale));
                    $height = max(1, (int) round($source->getImageHeight() * $scale));
                    $attempt->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
                }

                foreach ([85, 75, 65, 55, 45, 35] as $quality) {
                    $encoded = clone $attempt;
                    $encoded->setImageFormat('webp');
                    $encoded->setImageCompressionQuality($quality);
                    $blob = $encoded->getImageBlob();
                    $encoded->destroy();

                    if ($blob === '' || strlen($blob) > $targetBytes) {
                        continue;
                    }

                    if ($bestBlob === null || strlen($blob) < strlen($bestBlob)) {
                        $bestBlob = $blob;
                    }

                    if (strlen($blob) <= $targetBytes) {
                        $attempt->destroy();
                        $source->destroy();

                        return $this->put($directory, 'webp', $blob);
                    }
                }

                $attempt->destroy();
            }

            $source->destroy();

            if ($bestBlob !== null) {
                return $this->put($directory, 'webp', $bestBlob);
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function storeWithGd(
        UploadedFile $file,
        string $directory,
        int $targetBytes,
        int $maxDimension,
    ): ?string {
        $image = $this->createGdImage($file);

        if ($image === null) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        [$width, $height] = $this->scaledDimensions($width, $height, $maxDimension);

        $bestBlob = null;

        for ($scale = 1.0; $scale >= 0.35; $scale *= 0.85) {
            $scaledWidth = max(1, (int) round($width * $scale));
            $scaledHeight = max(1, (int) round($height * $scale));
            $canvas = imagecreatetruecolor($scaledWidth, $scaledHeight);

            if ($canvas === false) {
                continue;
            }

            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagecopyresampled(
                $canvas,
                $image,
                0,
                0,
                0,
                0,
                $scaledWidth,
                $scaledHeight,
                imagesx($image),
                imagesy($image),
            );

            foreach ([85, 75, 65, 55, 45, 35] as $quality) {
                $blob = $this->encodeGdWebp($canvas, $quality);

                if ($blob === null || $blob === '') {
                    continue;
                }

                if ($bestBlob === null || strlen($blob) < strlen($bestBlob)) {
                    $bestBlob = $blob;
                }

                if (strlen($blob) <= $targetBytes) {
                    imagedestroy($canvas);
                    imagedestroy($image);

                    return $this->put($directory, 'webp', $blob);
                }
            }

            imagedestroy($canvas);
        }

        imagedestroy($image);

        if ($bestBlob !== null) {
            return $this->put($directory, 'webp', $bestBlob);
        }

        return null;
    }

    private function createGdImage(UploadedFile $file): ?\GdImage
    {
        $path = $file->getPathname();

        return match ($file->getMimeType()) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            'image/bmp', 'image/x-ms-bmp' => function_exists('imagecreatefrombmp') ? (@imagecreatefrombmp($path) ?: null) : null,
            default => null,
        };
    }

    private function encodeGdWebp(\GdImage $image, int $quality): ?string
    {
        if (! function_exists('imagewebp')) {
            return null;
        }

        ob_start();
        $result = imagewebp($image, null, $quality);
        $blob = ob_get_clean();

        if (! $result || ! is_string($blob)) {
            return null;
        }

        return $blob;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function scaledDimensions(int $width, int $height, int $maxDimension): array
    {
        $longest = max($width, $height);

        if ($longest <= $maxDimension) {
            return [$width, $height];
        }

        $ratio = $maxDimension / $longest;

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }

    private function fitWithinDimension(Imagick $image, int $maxDimension): void
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $longest = max($width, $height);

        if ($longest <= $maxDimension) {
            return;
        }

        $image->thumbnailImage($maxDimension, $maxDimension, true);
    }

    private function put(string $directory, string $extension, string $contents): string
    {
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
