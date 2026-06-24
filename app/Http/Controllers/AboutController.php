<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\StoreSettingService;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(StoreSettingService $storeSettings): View
    {
        $page = Page::query()
            ->where('slug', Page::SLUG_ABOUT)
            ->where('is_active', true)
            ->firstOrFail();

        $settings = $storeSettings->current();

        return view('storefront.about', [
            'page' => $page,
            'settings' => $settings,
        ]);
    }
}
