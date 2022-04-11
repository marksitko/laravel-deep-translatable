<?php

namespace MarkSitko\DeepTranslatable\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationDictionary extends Model
{
    protected $fillable = [
        'key',
        'translations',
    ];

    protected $casts = [
        'key' => 'string',
        'translations' => 'array',
    ];

    public function hasTranslation(string $locale): bool
    {
        if ($this->isDefaultLocale($locale)) {
            return true;
        }

        return isset($this->translations[$locale]);
    }

    public function getTranslation(string $locale): string | null
    {
        if ($this->isDefaultLocale($locale)) {
            return $this->key;
        }

        return $this->translations[$locale] ?? null;
    }

    public function isDefaultLocale(string $locale): bool
    {
        return config('deep-translatable.source_lang') === $locale;
    }
}
