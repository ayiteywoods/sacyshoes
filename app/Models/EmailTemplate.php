<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public const SLUG_WELCOME = 'welcome';

    public const SLUG_ORDER_CREATED = 'order_created';

    public const SLUG_PAYMENT_RECEIVED = 'payment_received';

    public const SLUG_ORDER_STATUS = 'order_status';

    public const SLUG_ORDER_CANCELLED = 'order_cancelled';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'subject',
        'body',
        'placeholders',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return list<string>
     */
    public function placeholderList(): array
    {
        return $this->placeholders ?? [];
    }
}
