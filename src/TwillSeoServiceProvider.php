<?php

namespace WebWhales\TwillSeo;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class TwillSeoServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/metadata.php', 'metadata');
        $this->mergeConfigFrom(__DIR__.'/../config/seotools.php', 'seotools');
    }

    public function boot(): void
    {
        $this->loadResources();
        $this->extendBlade();
    }

    private function extendBlade(): void
    {
        Blade::include('twill-seo::includes.metadata-fields', 'metadataFields');
        Blade::include('twill-seo::includes.metadata-settings', 'metadataSettings');
    }

    private function loadResources(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views/vendor', 'twill-seo');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'twill-seo');

        $this->publishes([
            __DIR__.'/../config/metadata.php' => config_path('metadata.php'),
            __DIR__.'/../config/seotools.php' => config_path('seotools.php'),
        ], 'twill-seo-config');

        $this->publishes([
            __DIR__.'/../resources/views/twill' => resource_path('views/twill'),
        ], 'twill-seo-app-views');

        $this->publishes([
            __DIR__.'/../resources/views/vendor' => resource_path('views/vendor/twill-seo'),
        ], 'twill-seo-views');

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/twill-metadata'),
        ], 'twill-seo-lang');
    }
}
