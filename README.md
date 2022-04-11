# Automatic translatables Eloquent models with DeepL

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marksitko/laravel-deep-translatable.svg?style=flat-square)](https://packagist.org/packages/marksitko/laravel-deep-translatable)
[![Total Downloads](https://img.shields.io/packagist/dt/marksitko/laravel-deep-translatable.svg?style=flat-square)](https://packagist.org/packages/marksitko/laravel-deep-translatable)

Laravel-Deep-Translatable is more than just an API Wrapper for DeepL. It contains a trait to make Eloquent models automatic translatable. So you can easily publish a Blog post (or whatever) and create multiple language versions of it quite automatically. If you need to adjust or refine the translation, you can do so using the provided `updateTranslation` method.

## Installation

Install the package via composer:

```bash
composer require marksitko/laravel-deep-translatable
```

Laravel-Deep-Translatable comes with package discovery and Laravel will register the service provider automatically. Just in case you wanna add it manually, you should provide it in config/app.php

**Service provider**
``` php 
'providers' => [
    //...
    MarkSitko\DeepTranslatable\DeepTranslatableServiceProvider::class,
];
```

Next you should publish the configuration and migration.
``` bash
$ php artisan vendor:publish --provider="MarkSitko\DeepTranslatable\DeepTranslatableServiceProvider"
```

Run migrations
``` bash
$ php artisan migrate
```

## Configuration
You have at least to provide a DeepL API key in your `.env` file. 
[Read how to accessing the DeepL API](https://www.deepl.com/docs-api/accessing-the-api/)
```
DEEP_TRANSLATABLE_AUTH_KEY=YOUR_GENERATED_API_KEY_FROM_DEEP_L
```

Optional configurations:
```
# Swaps the api url if you have subscriped to the pro plan
DEEP_TRANSLATABLE_USE_PRO_VERSION=false

# The default source language
DEEP_TRANSLATABLE_SOURCE_LANG=en

# The query parameter which indicates to switch beteween translations
DEEP_TRANSLATABLE_QUERY_ATTRIBUTE=lang

# Translation can be passed to the queue, its recommended if you translation bulks
DEEP_TRANSLATABLE_USE_QUEUE=false

# Specific queue name
DEEP_TRANSLATABLE_QUEUE_NAME=null
```

## Usage
First implement the `Translatable` Interface, add the `UseDeepTranslations` Trait and define your `translatable` keys.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use MarkSitko\DeepTranslatable\UseDeepTranslations;
use MarkSitko\DeepTranslatable\Contracts\Translatable;

class Post extends Model implements Translatable
{
    use UseDeepTranslations;

    protected $fillable = [
        'title',
        'body',
    ];

    protected $translatable = [
        'title',
        'body',
    ];
}
```

**Translate and persist**
Call `translateViaDictionary()` method on your model with your target language. 
```php
use App\Models\Post;
use MarkSitko\DeepTranslatable\Lang;

$post = Post::create([
    'title' => 'My awesome title!',
    'body' => 'This is the main content',
]);

$post->translateViaDictionary(Lang::DE);

// to swap the post language, 
// simply call your url with an additional query 'lang' parameter.
// https://yousite.test/posts/1?lang=de

// It would return the post translatable keys:

// titel => 'Mein genialer Titel!'
// body => 'Dies ist der Hauptinhalt'
```

**Translate without persisting**
```php
use App\Models\Post;
use MarkSitko\DeepTranslatable\Lang;

Post::first()->translate(Lang::DE);
```

**Retrieve all translations form a model**
```php
Post::first()->translations;
```

**Just use the API Client without Eloquent models**
```php
use MarkSitko\DeepTranslatable\DeepL;
use MarkSitko\DeepTranslatable\Lang;

DeepL::translate('Hello World!', Lang::DE); // <- returns 'Hallo Welt!'
DeepL::translate('Los gehts!', Lang::EN, ['source_lang' => Lang::DE]); // <- returns 'Let's go'
```
The third param in the translate method is an optional `$options` array. Here you can define a specific source language or additional query options.

**Translate value from stored json object**
If you'r model has a json column which stored a value which you would like to translate, you can define the path with dot notaions in the `$translatable` property.
```php
class Fields extends Model implements Translatable
{
    use UseDeepTranslations;
    /**
     * Imagine you have stored the following json object in the option column
     * 
     * {
     *  option: {
     *      nested: {
     *          key: {
     *              value: 'I would like to be translated', 
     *          },
     *          other_key: {
     *              value: 'I should not be translated', 
     *          },
     *      },
     *  }
     * }
     * 
     * */

    protected $translatable = [
        'option.nested.key.value', 
    ];
}
```

## How it works
If you call `translateViaDictionary()` on your Eloquent model, each  translation is stored in a special `translation_dictionaries` table. The source text is stored as a key and each translation is stored as a json with the specified target language code. This concept was implemented to avoid requests for already translated strings. 

For each translation call, the `translation_dictionaries` table is first checked to see if a translation already exists for the specified target language. If a translation exists it will be returned from the database, if not it will be retrieved from DeepL. You can also use this method to keep your own dictionary for specific words.

## Push translation calls to the queue
By default the `translateViaDictionary()` method is executed synchronously. For one model or simple strings this is fine, but if you plan to perform bulk translations it is recommended to use the queue for translate and persisting models.
- Setup your queue
- In your `.env` file set  `DEEP_TRANSLATABLE_USE_QUEUE=true`
- If you would use a specific queue, you can define the name by `DEEP_TRANSLATABLE_QUEUE_NAME=translating`

## Switch between translations
To swap translatables keys on your model, you just need to call your site with an additional query parameter which defines your target language code. 

**For example:**
If your default site for a particular post would be:
`https://yousite.com/posts/1`
then call it up for the german translation as follows:
`https://yousite.test/posts/1?lang=de`

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
