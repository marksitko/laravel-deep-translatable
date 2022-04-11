<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deep-Translatable Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for laravel deep translatables.
    | It is required to define at least the 'DEEP_TRANSLATABLE_AUTH_KEY'
    | variable. All other variables are optional and free to adjust.
    |
    | To learn more: https.//github.com/marksitko/deep-translatable
    */

    'auth_key' => env('DEEP_TRANSLATABLE_AUTH_KEY', null),
    'use_pro_version' => env('DEEP_TRANSLATABLE_USE_PRO_VERSION', true),
    'source_lang' => env('DEEP_TRANSLATABLE_SOURCE_LANG', 'en'),
    'query_attribute' => env('DEEP_TRANSLATABLE_QUERY_ATTRIBUTE', 'lang'),
    'use_queue' => env('DEEP_TRANSLATABLE_USE_QUEUE', false),
    'queue_name' => env('DEEP_TRANSLATABLE_QUEUE_NAME', null),
    'translation_model' => \MarkSitko\DeepTranslatable\Models\Translation::class,
    'translation_dictionary_model' => \MarkSitko\DeepTranslatable\Models\TranslationDictionary::class,
];
