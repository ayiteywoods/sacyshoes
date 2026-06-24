<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailDispatch extends Model
{
    protected $fillable = [
        'email_template_slug',
        'recipient',
        'order_id',
        'is_test',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_test' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
