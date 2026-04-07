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
                $unreadDeliveryCount = auth()->user()
                    ->deliveryMessages()
                    ->where('type', 'delivery')
                    ->wherePivot('is_read', 0)
                    ->count();

                $unreadNotificationCount = auth()->user()
                    ->deliveryMessages()
                    ->where('type', 'notification')
                    ->wherePivot('is_read', 0)
                    ->count();

                $view->with('unreadDeliveryCount', $unreadDeliveryCount);
                $view->with('unreadNotificationCount', $unreadNotificationCount);
            }
        });
    }
}
