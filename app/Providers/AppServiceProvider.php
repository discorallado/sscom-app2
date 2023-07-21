<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Foundation\Vite;
use Illuminate\Support\Facades\Schema as Shema;



use Illuminate\Support\ServiceProvider;
// // use Illuminate\Support\ServiceProvider;
// use Schema;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;



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
        // Filament
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      if ($this->app->isLocal()) {
          $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
      }

        Filament::registerScripts([
            'https://cdn.jsdelivr.net/npm/pace-js@latest/pace.min.js',
        ], true);

        Filament::registerStyles([
            // 'https://codebyzach.github.io/pace/assets/css/templates/pace-theme-center-atom.tmpl.css'
            asset('css/app.css'),
        ]);

        Filament::serving(function () {
        // Using Vite
        Filament::registerTheme(
            app(Vite::class)('resources/css/app.css'),
        );
    });

        Model::unguard();
        Shema::defaultStringLength(191);


        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
