<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Collection;

class PageService
{
    /**
     * @return Collection<int, Page>
     */
    public function footerPages(string $group): Collection
    {
        return Page::query()
            ->where('footer_group', $group)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    public function findActiveBySlug(string $slug): ?Page
    {
        return Page::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
