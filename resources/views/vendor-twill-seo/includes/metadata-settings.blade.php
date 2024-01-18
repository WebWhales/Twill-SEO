@formField('input', [
    'name' => 'site_title',
    'label' => twillTrans('twill-seo::settings.fields.site_title.label'),
    'note' => twillTrans('twill-seo::settings.fields.site_title.note'),
    'textLimit' => '80',
    'translated' => true,
])

@formField('medias', [
    'name' => 'default_social_image',
    'label' => twillTrans('twill-seo::settings.fields.og_image.label'),
    'translated' => true,
])

@formField('input', [
    'name' => 'site_twitter',
    'label' => twillTrans('twill-seo::settings.fields.twitter_handle.label'),
    'note' => twillTrans('twill-seo::settings.fields.twitter_handle.note'),
    'textLimit' => '80',
    'translated' => true,
])
