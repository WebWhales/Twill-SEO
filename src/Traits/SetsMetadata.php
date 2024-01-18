<?php

namespace WebWhales\TwillSeo\Traits;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Database\Eloquent\Model;
use WebWhales\TwillSeo\Models\Behaviours\HasMetadata;

trait SetsMetadata
{
    public function setMetadataFromTwillModel(Model $describable): void
    {
        /** @phpstan-ignore-next-line */
        /** @var Model&HasMetadata $describable */
        $metadata = $describable->metadata()->firstOrCreate();

        SEOTools::setTitle($metadata->field('title'));

        if ($metadata->field('description')) {
            SEOTools::setDescription($metadata->field('description'));
        }

        SEOTools::opengraph()->setTitle($metadata->field('og_title'));

        if ($metadata->field('og_description')) {
            SEOTools::opengraph()->setDescription($metadata->field('og_description'));
        }

        SEOTools::opengraph()->addProperty('type', $metadata->field('og_type'));

        if ($metadata->field('og_image')) {
            SEOTools::opengraph()->addImage($metadata->field('og_image'));
        }

        SEOTools::opengraph()->setUrl(request()->url());

        if ($metadata->field('canonical_url')) {
            SEOTools::metatags()->setCanonical($metadata->field('canonical_url'));
        }

        $noindex  = $metadata->field('noindex');
        $nofollow = $metadata->field('nofollow');

        if ($noindex && $nofollow) {
            /** @phpstan-ignore-next-line */
            SEOTools::metatags()->setRobots('noindex, nofollow');
        } elseif ($noindex || $nofollow) {
            /** @phpstan-ignore-next-line */
            SEOTools::metatags()->setRobots($noindex ? 'noindex' : 'nofollow');
        }
    }
}
