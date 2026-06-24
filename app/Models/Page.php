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

    public const SLUG_ABOUT = 'about-us';

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

    /**
     * @return array{eyebrow: string, description: string, icon: string, stats: list<array{value: string, label: string, icon: string, tone?: string}>}
     */
    public function heroConfig(): array
    {
        $updated = $this->updated_at?->format('M Y') ?? '—';

        return match ($this->slug) {
            self::SLUG_PRIVACY => [
                'eyebrow' => 'Legal',
                'description' => 'How we collect, use, and protect your personal information when you shop with us.',
                'icon' => 'shield',
                'stats' => [
                    ['value' => $updated, 'label' => 'Updated', 'icon' => 'calendar', 'tone' => 'red'],
                    ['value' => 'SSL', 'label' => 'Encrypted', 'icon' => 'shield', 'tone' => 'white'],
                    ['value' => 'GH', 'label' => 'Compliant', 'icon' => 'document', 'tone' => 'red'],
                ],
            ],
            self::SLUG_TERMS => [
                'eyebrow' => 'Legal',
                'description' => 'The terms and conditions that govern purchases, delivery, and use of our store.',
                'icon' => 'document',
                'stats' => [
                    ['value' => $updated, 'label' => 'Updated', 'icon' => 'calendar', 'tone' => 'red'],
                    ['value' => 'Fair', 'label' => 'Policy', 'icon' => 'document', 'tone' => 'white'],
                    ['value' => 'GH', 'label' => 'Jurisdiction', 'icon' => 'shield', 'tone' => 'red'],
                ],
            ],
            self::SLUG_DELIVERY => [
                'eyebrow' => 'Customer care',
                'description' => 'Delivery timelines, covered regions, and what to expect when your order is on the way.',
                'icon' => 'truck',
                'stats' => [
                    ['value' => 'Ghana', 'label' => 'Nationwide', 'icon' => 'truck', 'tone' => 'red'],
                    ['value' => 'Tracked', 'label' => 'Orders', 'icon' => 'orders', 'tone' => 'white'],
                    ['value' => $updated, 'label' => 'Updated', 'icon' => 'calendar', 'tone' => 'red'],
                ],
            ],
            self::SLUG_RETURNS => [
                'eyebrow' => 'Customer care',
                'description' => 'How returns, exchanges, and refunds work for your peace of mind.',
                'icon' => 'bag',
                'stats' => [
                    ['value' => 'Easy', 'label' => 'Returns', 'icon' => 'bag', 'tone' => 'red'],
                    ['value' => 'Support', 'label' => 'Help', 'icon' => 'chat', 'tone' => 'white'],
                    ['value' => $updated, 'label' => 'Updated', 'icon' => 'calendar', 'tone' => 'red'],
                ],
            ],
            self::SLUG_CONTACT => [
                'eyebrow' => 'Customer care',
                'description' => 'Reach our team for order help, product questions, or partnership enquiries.',
                'icon' => 'chat',
                'stats' => [
                    ['value' => 'Email', 'label' => 'Support', 'icon' => 'chat', 'tone' => 'red'],
                    ['value' => 'GH', 'label' => 'Based', 'icon' => 'building', 'tone' => 'white'],
                    ['value' => 'Fast', 'label' => 'Response', 'icon' => 'shield', 'tone' => 'red'],
                ],
            ],
            default => [
                'eyebrow' => 'Information',
                'description' => 'Helpful information from '.config('shop.store_name', 'SACYSHOES').'.',
                'icon' => 'info',
                'stats' => [
                    ['value' => $updated, 'label' => 'Updated', 'icon' => 'calendar', 'tone' => 'red'],
                    ['value' => 'Info', 'label' => 'Policy', 'icon' => 'document', 'tone' => 'white'],
                    ['value' => 'GH', 'label' => 'Store', 'icon' => 'building', 'tone' => 'red'],
                ],
            ],
        };
    }

    public function isLegalPage(): bool
    {
        return in_array($this->slug, [self::SLUG_PRIVACY, self::SLUG_TERMS], true);
    }

    /**
     * @return list<array{title: string, id: string, level: int}>
     */
    public function tableOfContents(): array
    {
        $sections = [];

        if (! preg_match_all('/^(#{2,3})\s+(.+)$/m', $this->body ?? '', $matches, PREG_SET_ORDER)) {
            return $sections;
        }

        foreach ($matches as $match) {
            $title = trim($match[2]);

            $sections[] = [
                'title' => $title,
                'id' => \Illuminate\Support\Str::slug($title),
                'level' => strlen($match[1]),
            ];
        }

        return $sections;
    }

    public function renderedBody(): string
    {
        $html = \Illuminate\Support\Str::markdown($this->body ?? '');

        $withIds = preg_replace_callback(
            '/<h([23])>(.*?)<\/h\1>/',
            function (array $match): string {
                $id = \Illuminate\Support\Str::slug(strip_tags($match[2]));

                return '<h'.$match[1].' id="'.$id.'">'.$match[2].'</h'.$match[1].'>';
            },
            $html,
        );

        return $withIds ?? $html;
    }
}
