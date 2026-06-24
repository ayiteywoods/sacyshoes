<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\FavoriteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function __construct(
        protected FavoriteService $favorites
    ) {}

    public function index(): View
    {
        $products = $this->favorites->productsFor(auth()->user());

        return view('account.favorites.index', compact('products'));
    }

    public function toggle(Product $product): RedirectResponse
    {
        $added = $this->favorites->toggle(auth()->user(), $product);

        $message = $added
            ? 'Added to your favourites.'
            : 'Removed from your favourites.';

        return back()->with('success', $message);
    }
}
