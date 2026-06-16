<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(Page $page, PageService $pages): View
    {
        abort_unless($page->is_active, 404);

        return view('storefront.pages.show', compact('page'));
    }
}
