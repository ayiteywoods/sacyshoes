<?php

namespace App\Services;

use App\Models\HomeSection;
use App\Models\Testimonial;
use Illuminate\Support\Collection;

class HomeContentService
{
    /**
     * @return Collection<string, HomeSection>
     */
    public function sections(): Collection
    {
        return HomeSection::query()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('key');
    }

    public function section(string $key): ?HomeSection
    {
        return HomeSection::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();
    }

    /**
     * @return Collection<int, Testimonial>
     */
    public function testimonials(): Collection
    {
        return Testimonial::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
