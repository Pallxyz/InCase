<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Subject;
use App\Observers\SubjectObserver;

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
        Subject::observe(SubjectObserver::class);
    }
}
