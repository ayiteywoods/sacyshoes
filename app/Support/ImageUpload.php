<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;

class ImageUpload
{
    public static function typesRule(): File
    {
        return File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif', 'heic', 'heif'])
            ->max(ImageStorage::INPUT_MAX_KILOBYTES);
    }

    /**
     * @return list<string|File>
     */
    public static function rules(int $targetKilobytes = 2048): array
    {
        return [
            'nullable',
            self::typesRule(),
        ];
    }

    public static function store(
        UploadedFile $file,
        string $directory,
        int $targetKilobytes,
        int $maxDimension = 2000,
    ): string {
        return app(ImageStorage::class)->store($file, $directory, $targetKilobytes, $maxDimension);
    }

    public static function targetLabel(int $targetKilobytes): string
    {
        if ($targetKilobytes % 1024 === 0) {
            return ($targetKilobytes / 1024).' MB';
        }

        return $targetKilobytes.' KB';
    }
}
