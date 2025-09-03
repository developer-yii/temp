<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $unreadCount = auth()->user()
                    ->deliveryMessages()
                    ->wherePivot('is_read', 0)
                    ->count();

                $view->with('unreadCount', $unreadCount);
            }
        });
    }
}
