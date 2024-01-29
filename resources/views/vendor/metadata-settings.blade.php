@formField('input', [
    'name' => 'site_title',
    'label' => twillTrans('twill-seo::settings.fields.site_title.label'),
    'note' => twillTrans('twill-seo::settings.fields.site_title.note'),
    'textLimit' => '80',
    'translated' => true,
])

@formField('input', [
    'name' => 'site_title_separator',
    'label' => twillTrans('twill-seo::settings.fields.site_title_separator.label'),
    'note' => twillTrans('twill-seo::settings.fields.site_title_separator.note'),
    'textLimit' => '3',
    'default' => '|',
])

@formField('medias', [
    'name' => 'default_social_image',
    'label' => twillTrans('twill-seo::settings.fields.og_image.label'),
    'translated' => true,
])
