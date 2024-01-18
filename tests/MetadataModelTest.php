<?php

namespace WebWhales\TwillSeo\Tests;

use A17\Twill\Models\Model;
use WebWhales\TwillSeo\Models\Behaviours\HasMetadata;
use WebWhales\TwillSeo\Repositories\MetadataRepository;
use WebWhales\TwillSeo\Tests\Helpers\AnonymousModule;

class MetadataModelTest extends TestCase
{
    public function test_meta_data_is_used_from_a_model(): void
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

        $this->assertNotNull($model->refresh()->metadata);
        $this->assertEquals('Meta data test title', $model->metadata->field('title'));
    }

    public function test_model_data_is_used_as_a_fallback_for_meta_data(): void
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

        $this->assertNotNull($model->metadata);
        $this->assertEquals('Test title', $model->metadata->field('title'));
    }
}
