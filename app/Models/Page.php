<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public const SLUG_PRIVACY = 'privacy-policy';

    public const SLUG_TERMS = 'terms-and-conditions';

    public const SLUG_DELIVERY = 'delivery-info';

    public const SLUG_RETURNS = 'returns-policy';

    public const SLUG_CONTACT = 'contact-us';

    public const FOOTER_CUSTOMER_CARE = 'customer_care';

    public const FOOTER_LEGAL = 'legal';

    protected $fillable = [
        'slug',
        'title',
        'body',
        'footer_group',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
