<?php

namespace App\Providers;

use App\Models\Setting;
use App\Observers\SettingsObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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
        if (\config('app.redirect_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        if (\config('app.redirect_https')) {
            \URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        Setting::observe(SettingsObserver::class);

        Model::preventLazyLoading(app()->environment('development'));

        if (app()->environment('development')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }

}
