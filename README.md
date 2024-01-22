# Twill Metadata

[![Latest Version on Packagist](https://img.shields.io/packagist/v/webwhales/twill-seo.svg?style=flat-square)](https://packagist.org/packages/webwhales/twill-seo)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/webwhales/twill-seo.svg?style=flat-square)](https://packagist.org/packages/webwhales/twill-seo)

## What it does
This package offers a simple way to add SEO metadata to your [Twill](https://twill.io/) models by providing a drop-in fieldset to add all the required fields into your model edit form.  With sensible defaults, configurable fallbacks, and a global settings screen; this package should meet most of the needs for optimising meta tags within a site.

![default and expanded views of twill metadata fieldset](https://github.com/webwhales/twill-seo/blob/master/Twill-Seo-Preview.jpg)

## Requirements
This package requires Laravel 8 or higher, PHP8 or higher, and Twill 3.0 or higher.

## Upgrade Notes


## Installation

First you want to install this dependency using composer, you can do this by running the following command:

```shell script
$ composer require webwhales/twill-seo
```

Next we need to migrate the required database tables, you can do this by running the Laravel migration command:

```shell script
$ php artisan migrate
```

## Configuration

### Adding metadata to a Module
If your module requires SEO Metadata (e.g. Pages) then you need to update the following files:

1. ModuleModel
2. ModuleController
3. ModuleRepository

#### 1 – Update the Module Model
Set your model to use the `HasMetadata` trait, and add the public property `$metadataFallbacks`. 

Note: Your model also must include the HasMedias trait. This trait is used for generating for OpenGraph images.
```php
// App/Models/Page.php
class Page extends Model {

    use HasMetadata;
    use HasMedias;

    public array $metadataFallbacks = [];           
...
}

```
#### 2 – Update the Module Controller

In the Twill admin controller for the module you need to call the fieldset for the Metadata. We do this by making use of BladePartial.
```php
// App/Http/Controllers/Admin/PageController.php
public function getForm(TwillModelContract $model): Form
{
    $form = parent::getForm($model);
    
    // add your fields here...
    
    // copy the below to include metadata fieldset
    $form->addFieldset(
        \A17\Twill\Services\Forms\Fieldset::make()
        ->title(trans('twill-seo::form.titles.fieldset'))
        ->id('metadata')
        ->fields([
            \A17\Twill\Services\Forms\BladePartial::make()->view('twill-seo::includes.metadata-fields')
            ->withAdditionalParams([
                'metadata_card_type_options' => config('metadata.card_type_options'),
                'metadata_og_type_options' => config('metadata.opengraph_type_options'),
            ]),
        ])
    );
    
    return $form;
}
```

#### 3 – Update the Module Repository
Add `use HandleMetadata` onto your page repository.
```php
// App/Repositories/PageRepository.php
class PageRepository extends ModuleRepository
{
    use HandleBlocks, HandleSlugs, HandleMedias, HandleFiles, HandleRevisions, HandleMetadata;

    public function __construct(Page $model)
    {
        $this->model = $model;
    }
}
```

### Global Settings

Global settings for metadata allows you to set defaults for the following:
1. Meta Title – this will be appended after the page meta title.
2. Social Graph Image - this will render as the fallback sharing image
3. Twitter (X) handle for the social card.

#### 1 – Make sure the default social media image crop is in your twill.php config

```php
{{-- config/twill.php --}
return [
    'settings' => [
        'crops' => [
            'default_social_image' => [
                'default' => [
                    [
                        'name' => 'default',
                        'ratio' => 1.91 / 1,
                        'minValues' => [
                            'width' => 1200,
                            'height' => 627,
                        ],
                    ],
                ],
            ],
        ],
    ]
];
```

#### 2 – Create new settings file

Publish the application views for the package to create the settings file with the following command:
```shell script
  php artisan vendor:publish --provider="WebWhales\TwillSeo\TwillSeoServiceProvider" --tag=twill-seo-app-views
```

#### 3 – Add to Twill Settings Menu
```php
{{-- app/Providers/AppServiceProvider.php --}}
public function boot(): void
{
    // Register Twill Settings
    TwillAppSettings::registerSettingsGroups(
        SettingsGroup::make()->name('seo')->label(trans('twill-seo::form.titles.fieldset')),
    );

}
```


## How to use Metatags in your frontend code.

Firstly we need to set the Metadata.

Let's assume you have a module called Pages which has Metadata linked.

In your frontend routes you will have something like:
```php
Route::get('{slug}', \App\Http\Controllers\Frontend\PageController::class)
    ->name('frontend.page')->where('slug', '.*');
```
In the controller for your frontend application you can add the trait `SetsMetadata` and then use the `setMetadata()` function to set the metadata.  

```php
<?php
// App/Http/Controllers/PageController.php
class PageController extends Controller
{
    use SetsMetadata;

    public function __invoke(string $slug, \App\Repositories\PageRepository $pageRepository): \Illuminate\View\View
    {
        $page = $pageRepository->forSlug($slug);
        
        abort_if(! $page, 404);
        
        // Set the page metadata
        $this->setMetadata($page);
        
        // return your view
        return view('site.pages.page', ['page' => $page]);
    }
}
```

Under the hood this uses the [artesaos/seotools](https://github.com/artesaos/seotools) package to set and display metadata. So you are free to not use the above helper, and manually set the meta tags as required. Or you can use the helper, and then use the methods provided by the package to amend the tags.

## Outputting meta tags in your frontend
See the documentation for [artesaos/seotools](https://github.com/artesaos/seotools) for more granular options, but the easiest way is shown below:
```blade
{{-- resources/views/layouts/site.blade.php --}}
<html lang="en">
<head>

    {!! SEO::generate() !!}

</head>
```

## Customisation

You can publish the config for the package with the following command:
```shell script
  php artisan vendor:publish --provider="WebWhales\TwillSeo\TwillSeoServiceProvider" --tag=twill-seo-config
```

Within the config file is a fallbacks array, which can be customised according to your needs.  This is a global config and will apply to all models that use the HasMetadata trait. i.e. in the config below if no description is entered in the metadata description field, the content field on the model will be used as the metadata description (all tags will be stripped).
```php
// Key is the metadata attribute,
// Value is the model attribute it will fall back to if metadata value is empty
'fallbacks' => [
    'title' => 'title',
    'description' => 'content',
    'og_type' => 'metadataDefaultOgType',
    'card_type' => 'metadataDefaultCardType',
],
```

To provide different fallback configurations to different models with the HasMetadata trait you can use the same array in the public $metadataFallBacks property on the model.
```php
// App/Models/Page.php
class Page extends Model {

    use HasMetadata;

    public array $metadataFallbacks = [
        'title' => 'name',
        'description' => 'bio',
    ];             
...
}
```
The two arrays are merged, so you only need to include the keys you wish to override from the global config.

If you wish to provide a default OpenGraph Type and Twitter Card Type then you can add the following two public properties to your model:

```php
    public $metadataDefaultOgType = 'website';
    public $metadataDefaultCardType = 'summary_large_image';
```

You can publish the views for the package with the following command:
```shell script
  php artisan vendor:publish --provider="WebWhales\TwillSeo\TwillSeoServiceProvider" --tag=twill-seo-views
```

You can publish the language files for the package with the following command:
```shell script
  php artisan vendor:publish --provider="WebWhales\TwillSeo\TwillSeoServiceProvider" --tag=twill-seo-lang
```
