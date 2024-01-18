<?php

namespace WebWhales\TwillSeo\Repositories;

use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Repositories\Behaviors\HandleTranslations;
use A17\Twill\Repositories\ModuleRepository;
use WebWhales\TwillSeo\Models\Behaviours\HasMetadata;
use WebWhales\TwillSeo\Models\Metadata;

class MetadataRepository extends ModuleRepository
{
    use HandleTranslations;

    public function __construct(Metadata $model)
    {
        $this->model = $model;
    }

    public function updateOrCreateForModel(TwillModelContract $model, array $fields): Metadata
    {
        /** @phpstan-ignore-next-line */
        /** @var TwillModelContract&HasMetadata $model */
        $metadata = $model->metadata ?? $model->metadata()->create();

        /** @phpstan-ignore-next-line */
        return $this->update($metadata->id, $fields);
    }
}
