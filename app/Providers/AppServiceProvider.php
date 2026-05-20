<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Result;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\Paginator;


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
    // public function boot(): void
    // {
    //     Paginator::useBootstrapFive();
    // }
    public function boot(): void
    {

        View::composer('*', function ($view) {

            $now = Carbon::now();

            $lastMinute = floor($now->minute / 15) * 15;

            $lastDrawTime = $now->copy()
                ->minute($lastMinute)
                ->second(0);

            $lastResults = Result::where('draw_time', $lastDrawTime)
                ->pluck('result_number', 'series')
                ->toArray();

            $view->with('lastResults', $lastResults);
        });
        Paginator::useBootstrapFive();
    }
}
