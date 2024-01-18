<?php

namespace WebWhales\TwillSeo\Tests;

use A17\Twill\Models\Model;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Support\Facades\Route;
use WebWhales\TwillSeo\Http\Middleware\LoadMetadata;
use WebWhales\TwillSeo\Models\Behaviours\HasMetadata;
use WebWhales\TwillSeo\Repositories\MetadataRepository;
use WebWhales\TwillSeo\Tests\Helpers\AnonymousModule;
use WebWhales\TwillSeo\Traits\SetsMetadata;

class SeoDataTest extends TestCase
{
    public function test_meta_data_is_used_for_seo_tags(): void
    {
        $module = AnonymousModule::make('posts', $this->app)
            ->withModelTraits([HasMetadata::class])
            ->boot();

        /** @phpstan-ignore-next-line */
        /** @var Model&HasMetadata $model */
        $model = $module->getRepository()->create([
            'title' => 'Test title',
        ]);
        $this->app?->make(MetadataRepository::class)->updateOrCreateForModel($model, [
            'title' => ['en' => 'Meta data test title'],
        ]);

        $controllerObject = new class {
            use SetsMetadata;
        };

        $controllerObject->setMetadataFromTwillModel($model);

        $this->assertEquals('Meta data test title', SEOTools::getTitle());
    }

    public function test_model_data_is_used_as_a_fallback_for_seo_tags(): void
    {
        $module = AnonymousModule::make('posts', $this->app)
            ->withModelTraits([HasMetadata::class])
            ->boot();

        /** @phpstan-ignore-next-line */
        /** @var Model&HasMetadata $model */
        $model = $module->getRepository()->create([
            'title' => 'Test title',
        ]);
        $model->metadata()->create();

        $controllerObject = new class {
            use SetsMetadata;
        };

        $controllerObject->setMetadataFromTwillModel($model);

        $this->assertEquals('Test title', SEOTools::getTitle());
    }

    public function test_default_seo_meta_data_is_rendered(): void
    {
        $this->updateTwillAppSettings('seo', 'metadata', [
            'site_title' => ['en' => 'Default site title'],
        ]);

        Route::view('test-route', 'page_with_seo_metadata')
            ->middleware(LoadMetadata::class);

        $this->get('test-route')
            ->assertSee('<title>Default site title</title>', false);
    }

    public function test_model_seo_meta_data_is_rendered(): void
    {
        $this->updateTwillAppSettings('seo', 'metadata', [
            'site_title' => ['en' => 'Default site title'],
        ]);

        $module = AnonymousModule::make('posts', $this->app)
            ->withModelTraits([HasMetadata::class])
            ->boot();

        /** @phpstan-ignore-next-line */
        /** @var Model&HasMetadata $model */
        $model = $module->getRepository()->create([
            'title' => 'Test title',
        ]);
        $this->app?->make(MetadataRepository::class)->updateOrCreateForModel($model, [
            'title' => ['en' => 'Meta data test title'],
        ]);

        Route::get('test-route', function () use ($model) {
            $controllerObject = new class {
                use SetsMetadata;
            };

            $controllerObject->setMetadataFromTwillModel($model);

            return view('page_with_seo_metadata');
        })->middleware(LoadMetadata::class);

        $this->get('test-route')
            ->assertSee('<title>Meta data test title | Default site title</title>', false);
    }
}
