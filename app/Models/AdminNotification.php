<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    protected $fillable = [
        'reference_key',
        'type',
        'title',
        'message',
        'url',
        'read_at',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'dismissed_at' => 'datetime',
        ];
    }

    public function isDismissed(): bool
    {
        return $this->dismissed_at !== null;
    }
}
