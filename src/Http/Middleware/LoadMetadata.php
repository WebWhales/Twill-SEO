<?php

namespace WebWhales\TwillSeo\Http\Middleware;

use A17\Twill\Facades\TwillAppSettings;
use Closure;

class LoadMetadata
{
    public function handle(\Illuminate\Http\Request $request, Closure $next): mixed
    {
        $this->loadMetadataConfig();

        return $next($request);
    }

    private function loadMetadataConfig(): void
    {
        $separator = TwillAppSettings::getTranslated('seo.metadata.site_title_separator');

        config()->set('seotools.meta.defaults.separator', $separator ? " $separator " : ' | ');
        config()->set('seotools.meta.defaults.title', TwillAppSettings::getTranslated('seo.metadata.site_title'));
    }
}
