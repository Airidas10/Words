<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;

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
        $user = Auth::user();
        Inertia::share([
            'authToken' => function () {
                return Session::get('token');
            },

            'user' => function () {
                return Auth::check() ? Auth::user()->only('id', 'username', 'is_admin') : null;
            },
        ]);
    }
}
