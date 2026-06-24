<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingRegion extends Model
{
    protected $fillable = [
        'name',
        'is_accra',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_accra' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(ShippingOption::class)->orderBy('sort_order')->orderBy('name');
    }
}
