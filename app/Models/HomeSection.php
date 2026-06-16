<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeSection extends Model
{
    public const KEY_HERO = 'hero';

    public const KEY_FREE_DELIVERY = 'free_delivery_banner';

    public const KEY_SHOP_CATEGORY = 'shop_by_category';

    public const KEY_CTA = 'cta';

    public const KEY_NEW_ARRIVALS = 'new_arrivals';

    public const KEY_TESTIMONIALS_HEADER = 'testimonials_header';

    public const KEY_DELIVERY_NOTICE = 'delivery_notice';

    protected $fillable = [
        'key',
        'name',
        'eyebrow',
        'title',
        'title_highlight',
        'body',
        'primary_label',
        'primary_url',
        'secondary_label',
        'secondary_url',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'images/') || str_starts_with($this->image_path, 'http')) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function resolvedUrl(?string $url): string
    {
        if (! $url) {
            return route('shop.index');
        }

        if (Str::startsWith($url, ['http://', 'https://', '/'])) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    }
}
