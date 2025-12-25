<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('partials.app', function ($view) {
            $cartCount = 0;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $cart = \App\Models\Cart::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
            } else {
                $sessionId = \Illuminate\Support\Facades\Session::getId();
                $cart = \App\Models\Cart::where('session_id', $sessionId)->first();
            }

            if ($cart) {
                // Count total quantity of items
                $cartCount = $cart->items->sum('quantity');
            }

            $view->with('cartCount', $cartCount);
        });
    }
}
