<?php

namespace App\Models;

use App\Enums\CategoryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CategoryStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function storefrontImageUrl(): ?string
    {
        $images = [
            'boots' => 'images/brand/boots.jpg',
            'formal' => 'images/brand/shoes.jpg',
            'sandals' => 'images/brand/sandals.jpg',
            'sneakers' => 'images/brand/sneakers.jpg',
        ];

        if (isset($images[$this->slug])) {
            return asset($images[$this->slug]);
        }

        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function storefrontIcon(): string
    {
        $icons = [
            'sneakers' => 'shoe',
            'formal' => 'heel',
            'sandals' => 'shoe',
            'boots' => 'shoe',
            'heels' => 'heel',
            'flats' => 'shoe',
            'bags' => 'bag',
        ];

        return $icons[$this->slug] ?? $icons[strtolower($this->name)] ?? 'shoe';
    }
}
