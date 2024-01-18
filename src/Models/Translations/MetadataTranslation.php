<?php

namespace WebWhales\TwillSeo\Models\Translations;

use A17\Twill\Models\Model;
use WebWhales\TwillSeo\Models\Metadata;

class MetadataTranslation extends Model
{
    /**
     * @var class-string
     */
    protected string $baseModuleModel = Metadata::class;
}
