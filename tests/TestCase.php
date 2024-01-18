<?php

namespace WebWhales\TwillSeo\Tests;

use A17\Twill\Facades\TwillAppSettings;
use A17\Twill\Services\Settings\SettingsGroup;
use A17\Twill\TwillServiceProvider;
use Artesaos\SEOTools\Providers\SEOToolsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use WebWhales\TwillSeo\TwillSeoServiceProvider;

class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            /** @phpstan-ignore-next-line */
            fn (string $model) => 'WebWhales\\TwillSeo\\Database\\Factories\\' . class_basename($model) . 'Factory'
        );

        $this->artisan('vendor:publish --tag=twill-seo-views');

        TwillAppSettings::getGroupForName('seo')->boot();
    }

    protected function getPackageProviders($app)
    {
        return [
            TwillServiceProvider::class,
            TwillSeoServiceProvider::class,
            SEOToolsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        require_once 'vendor/area17/twill/src/Helpers/migrations_helpers.php';

        config()->set('translatable.locales', ['en']);

        View::addLocation(__DIR__ . '/Helpers/views');

        // Register Twill Settings
        TwillAppSettings::registerSettingsGroups(
            SettingsGroup::make()->name('seo')->label(trans('twill-seo::form.titles.fieldset')),
        );
    }

    protected function updateTwillAppSettings(string $group, string $section, array $data): void
    {
        TwillAppSettings::getGroupDataForSectionAndName($group, $section)
            ->update(['content' => $data]);
    }
}
