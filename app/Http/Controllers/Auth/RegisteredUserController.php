<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $request->createUser();

        event(new Registered($user));

        Auth::login($user);

        app(CartService::class)->mergeGuestCartIntoUser($user);

        app(\App\Services\OrderNotificationService::class)->welcome($user);

        return redirect()->route('home');
    }
}
