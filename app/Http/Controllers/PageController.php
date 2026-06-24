<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\StoreSettingService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Page $page, StoreSettingService $storeSettings): View
    {
        abort_unless($page->is_active, 404);

        if ($page->slug === Page::SLUG_CONTACT) {
            return view('storefront.pages.contact', [
                'page' => $page,
                'settings' => $storeSettings->current(),
            ]);
        }

        if ($page->isLegalPage()) {
            return view('storefront.pages.legal', [
                'page' => $page,
                'settings' => $storeSettings->current(),
            ]);
        }

        return view('storefront.pages.show', compact('page'));
    }
}
