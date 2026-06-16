<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCartNotEmpty
{
    public function __construct(
        protected CartService $cart
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->cart->resolve()->items()->count() === 0) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Your cart is empty. Add products before checkout.');
        }

        return $next($request);
    }
}
