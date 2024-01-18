<?php

namespace WebWhales\TwillSeo\Models;

use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use WebWhales\TwillSeo\Models\Behaviours\HasMetadata;
use WebWhales\TwillSeo\Models\Translations\MetadataTranslation;

/**
 * @property int               $id
 * @property string|null       $og_type
 * @property string|null       $card_type
 * @property string|null       $noindex
 * @property string|null       $nofollow
 * @property Model&HasMetadata $meta_describable
 */
class Metadata extends Model
{
    use HasTranslation;

    /**
     * @var class-string
     */
    public string $translationModel = MetadataTranslation::class;

    /**
     * @var string[]
     */
    public $translatedAttributes = [
        'title',
        'description',
        'og_title',
        'og_description',
        'canonical_url',
    ];

    /**
     * @var string[]
     */
    public $fillable = [
        'og_type',
        'card_type',
        'noindex',
        'nofollow',
    ];

    public function meta_describable(): MorphTo
    {
        return $this->morphTo();
    }

    public function field(string $column): ?string
    {
        switch ($column) {
            case 'og_image':
                return $this->meta_describable->getSocialImageAttribute();
            case 'noindex':
            case 'nofollow':
                return $this->$column;
        }

        if (empty($this->$column)) {
            return $this->getFallbackValue($column);
        }

        return match ($column) {
            'og_type'   => $this->getOgTypeContent($this->$column),
            'card_type' => $this->getCardTypeContent($this->$column),
            default     => $this->$column,
        };
    }

    protected function getOgTypeContent(string $id): ?string
    {
        /** @var array<int, array{value: string, label: string}> $opengraphTypeOptions */
        $opengraphTypeOptions = config('metadata.opengraph_type_options');

        return collect($opengraphTypeOptions)
            ->firstWhere('value', $id)['label'] ?? null;
    }

    protected function getCardTypeContent(string $id): ?string
    {
        /** @var array<int, array{value: string, label: string}> $cardTypeOptions */
        $cardTypeOptions = config('metadata.card_type_options');

        return collect($cardTypeOptions)
            ->firstWhere('value', $id)['label'] ?? null;
    }

    protected function getFallbackValue(string $column): ?string
    {
        $fallbackColumn = $this->meta_describable->getMetadataFallbackColumn($column);

        // For opengraph title fall back to meta title
        if ($column === 'og_title') {
            return $this->field('title');
        }

        // For opengraph description fall back to meta description
        if ($column === 'og_description') {
            return $this->field('description');
        }

        if (empty($this->meta_describable->$fallbackColumn)) {
            return null;
        }

        return strip_tags($this->meta_describable->$fallbackColumn);
    }
}
