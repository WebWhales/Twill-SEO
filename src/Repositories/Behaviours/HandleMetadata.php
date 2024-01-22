<?php

namespace WebWhales\TwillSeo\Repositories\Behaviours;

use A17\Twill\Models\Contracts\TwillModelContract;
use A17\Twill\Models\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use WebWhales\TwillSeo\Repositories\MetadataRepository;

trait HandleMetadata
{
    // Prefix for metadata fields in form
    protected string $metadataFieldPrefix = 'metadata';

    // Fields with fixed default values that we want persisting to store if blank
    // N.B. this does not include those fields that fallback to another field when blank
    protected array $withDefaultValues = ['card_type', 'og_type'];

    /**
     * Handle saving of metadata fields from form submission.
     */
    public function afterSaveHandleMetadata(TwillModelContract $object, array $fields): void
    {
        // Due to the way twill handles adding data to VueX store
        // metadata will come through in individual fields metadata[title]... not in an array
        $fields = $this->getMetadataFields($fields);
        $fields = $this->setFieldDefaults($object, $fields);

        // Create or update the metadata object
        App::make(MetadataRepository::class)->updateOrCreateForModel($object, $fields);
    }

    public function getFormFieldsHandleMetadata(TwillModelContract $object, array $fields): array
    {
        // If the metadata object doesn't exist create it.  Every 'meta_describable' will need one entry.
        $metadata = $object->metadata ?? $object->metadata()->create();

        $metadata = $this->setFieldDefaults($object, $metadata);

        $fields['metadata'] = $metadata->attributesToArray();

        if ($metadata->translations != null && $metadata->translatedAttributes != null) {
            foreach ($metadata->translations as $translation) {
                foreach ($metadata->translatedAttributes as $attribute) {
                    unset($fields[$attribute]);
                    $fields['translations']["metadata[{$attribute}]"][$translation->locale] = $translation->{$attribute};
                }
            }
        }

        return $fields;
    }

    protected function getMetadataFields(array $fields): array
    {
        $metadataFields = [];

        foreach ($fields as $key => $value) {
            if ($this->isMetadataField($key)) {
                // transform metadata[xxxx] to xxxx
                $newKey = preg_replace('/'.$this->metadataFieldPrefix.'\[([^\]]*)\]/', '$1', $key);
                $metadataFields[$newKey] = $value;
            }
        }

        return $metadataFields;
    }

    protected function setFieldDefaults(TwillModelContract $object, array|Model $fields): array|Model
    {
        foreach ($this->withDefaultValues as $fieldName) {
            if (empty($fields[$fieldName])) {
                $property = 'metadataDefault'.Str::studly($fieldName);
                $fields[$fieldName] = $object->$property;
            }
        }

        return $fields;
    }

    protected function isMetadataField(string $key): bool
    {
        return str_starts_with($key, $this->metadataFieldPrefix);
    }
}
