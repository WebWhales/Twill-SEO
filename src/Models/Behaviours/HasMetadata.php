<?php

namespace WebWhales\TwillSeo\Models\Behaviours;

use A17\Twill\Facades\TwillAppSettings;
use A17\Twill\Models\Behaviors\HasBlocks;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;
use UnexpectedValueException;
use WebWhales\TwillSeo\Models\Metadata;

use function class_uses_recursive;
use function in_array;
use function property_exists;

/**
 * @mixin Model
 *
 * @property \WebWhales\TwillSeo\Models\Metadata $metadata
 */
trait HasMetadata
{
    use HasMedias;

    public bool $hasMetadata = true;

    private array $metadataSettings = [];

    public function metadata(): MorphOne
    {
        return $this->morphOne(Metadata::class, 'meta_describable');
    }

    public function getSocialImageAttribute(): ?string
    {
        if ($this->usesTrait(HasMedias::class) && $this->hasImage('og_image')) {
            return $this->socialImage('og_image');
        } elseif ($this->hasSpecifiedMetaFallbackImage('og_image')) {
            return $this->getSpecifiedMetadataFallbackImage('og_image');
        } elseif ($this->hasAnyImages()) {
            return $this->getDefaultMetadataFallbackImage();
        }

        $seoSettings = TwillAppSettings::getGroupDataForSectionAndName('seo', 'metadata');

        if ($seoSettings->hasImage('default_social_image', 'default')) {
            return $seoSettings->image('default_social_image', 'default');
        }

        return null;
    }

    public function hasSpecifiedMetaFallbackImage(string $key): bool
    {
        $fallback = $this->getMetadataFallbackColumn($key);

        return is_array($fallback) && array_key_exists('role', $fallback) && array_key_exists('crop', $fallback);
    }

    public function getSpecifiedMetadataFallbackImage(string $key): ?string
    {
        $fallback = $this->getMetadataFallbackColumn($key);

        $role = $fallback['role'] ?? '';
        $crop = $fallback['crop'] ?? '';

        if (empty($role) || empty($crop)) {
            throw new UnexpectedValueException("Metadata fallback with key $key must have settings for role and crop.");
        }

        return $this->socialImage($role, $crop, has_fallback: true);
    }

    public function getDefaultMetadataFallbackImage(): ?string
    {
        if ($this->hasAnyMedias()) {
            /** @var Model&HasMedias $this */
            /** @var \A17\Twill\Models\Media $media */
            $media = $this->medias()->first();

            /** @phpstan-ignore-next-line */
            return $this->socialImage($media->pivot->role, $media->pivot->crop, has_fallback: true);
        } elseif ($this->hasAnyBlockMedias()) {
            /** @var Model&HasBlocks $this */
            /** @var \A17\Twill\Models\Block $block */
            $block = $this->blocks()->has('medias')->first();
            $media = $block->medias()->first();

            /** @phpstan-ignore-next-line */
            return $block->socialImage($media->pivot->role, $media->pivot->crop, has_fallback: true);
        }

        return null;
    }

    public function hasAnyImages(): bool
    {
        return $this->hasAnyMedias() || $this->hasAnyBlockMedias();
    }

    public function hasAnyMedias(): bool
    {
        return $this->usesTrait(HasMedias::class) && $this->medias()->exists();
    }

    public function hasAnyBlockMedias(): bool
    {
        /** @var HasBlocks $this */
        return $this->usesTrait(HasBlocks::class) ? $this->blocks()->has('medias')->exists() : false;
    }

    public function getMetadataFallbackColumn(string $column): string|array|null
    {
        $fallbacks = $this->metadataSettings['metadata_fallbacks'];

        if (property_exists($this, 'metadataFallbacks')) {
            $fallbacks = array_merge($fallbacks, $this->metadataFallbacks);
        }

        return $fallbacks[$column] ?? null;
    }

    protected function initializeHasMetadata(): void
    {
        $this->metadataSettings['metadata_fallbacks'] = Arr::wrap(config('metadata.fallbacks'));
        $this->metadataSettings['medias_params']      = Arr::wrap(config('metadata.mediasParams'));

        // Add the default metadata from config into the $mediasParams array
        // by default adds in an 'og_image' role with a 'default' crop
        if (property_exists($this, 'mediasParams')) {
            if (isset($this->mediasParams) && is_array($this->mediasParams)) {
                $this->mediasParams = array_merge($this->mediasParams, config('metadata.mediasParams'));
            } else {
                $this->mediasParams = config('metadata.mediasParams');
            }
        }
    }

    private function usesTrait(string $trait): bool
    {
        return in_array($trait, class_uses_recursive($this));
    }
}
