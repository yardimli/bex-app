<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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
        View::composer('partials.sidebar', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                // The 'chatHeaders' relationship is already ordered on the User model,
                // so we can just access it directly.
                $chatHeaders = $user->chatHeaders;
                $view->with('chatHeaders', $chatHeaders);
            } else {
                // Provide an empty collection if the user is not logged in
                $view->with('chatHeaders', collect());
            }
        });
    }
}
