<?php

namespace App\Support;

class MailBranding
{
    public static function logoUrl(): string
    {
        foreach (['webp', 'png', 'jpg'] as $extension) {
            $path = public_path('images/brand/logo1.'.$extension);

            if (is_file($path)) {
                return asset('images/brand/logo1.'.$extension);
            }
        }

        return asset('images/brand/logo1.webp');
    }

    public static function storeName(): string
    {
        return (string) config('shop.store_name', config('app.name', 'SACYSHOES'));
    }
}
