<?php

namespace MarkSitko\DeepTranslatable;

use Exception;
use Illuminate\Support\Collection;
use MarkSitko\DeepTranslatable\DeepL;
use Illuminate\Database\Eloquent\Model;
use MarkSitko\DeepTranslatable\Jobs\Translate;
use MarkSitko\DeepTranslatable\Scopes\TranslationsScope;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use MarkSitko\DeepTranslatable\Actions\StoreTranslationAction;

/**
 * @return Model
 */
trait UseDeepTranslations
{
    /**
     * The Accessor is set in the "scopeWithTranslations" method.
     * After method call it is attached to the model.
     *
     * @var string
     */
    public string $translationAccessor = 'translation';

    public static function bootUseDeepTranslations(): void
    {
        self::verifyProperties();

        static::addGlobalScope(new TranslationsScope);

        static::retrieved(function (self $model) {
            if ($model->shouldUseOtherLanguage()) {
                $model->getTranslatableKeys()->each(function ($key) use ($model) {
                    $key = is_array($key) ? key($key) : $key;
                    if (! is_null($translation = $model->getTranslationKeyByLang($key, $model->getQueryLang()))) {
                        $model->{$key} = $translation;
                    }
                });
            }
        });

        static::deleting(function (self $model) {
            $model->translations()->delete();
        });
    }

    /**
     * Initialize the use deepL translations trait for an instance.
     *
     * @return void
     */
    public function initializeUseDeepTranslations()
    {
        $this->translatable = is_array($this->translatable)
                    ? collect($this->translatable)
                    : collect([$this->translatable]);
    }

    public function shouldUseOtherLanguage(): bool
    {
        return $this->getQueryLang() !== config('deep-translatable.source_lang');
    }

    public function getQueryLang(): string
    {
        return request()->query(config('deep-translatable.query_attribute'), config('deep-translatable.source_lang'));
    }

    public function translations(): MorphOneOrMany
    {
        return $this->morphMany(config('deep-translatable.translation_model'), 'translatable');
    }

    public function translate(string $targetLang): mixed
    {
        $translations = $this->getPrunedTranslatableKeys()
            ->mapWithKeys(function ($key) use ($targetLang) {
                if (is_array($key)) {
                    $result = DeepL::translateNested(
                        $this,
                        $key,
                        $targetLang,
                        'translate'
                    );

                    return [$result->get('key') => $result->get('data')];
                }

                return [$key => DeepL::translate($this->{$key}, $targetLang, [
                    'tag_handling' => 'xml',
                    'ignore_tags' => 'non-translatable',
                ])];
            });

        return $translations->count() === 1
            ? $translations->first()
            : $translations;
    }

    public function updateTranslation(string $key, string $lang, string $newTranslation): bool
    {
        $record = $this->translations()->where('key', $key)->firstOrFail();

        $languages = $record->languages;
        $languages[$lang] = $newTranslation;

        return $record->update([
            'languages' => $languages,
        ]);
    }

    public function getTranslatableKeys(): Collection
    {
        return $this->translatable;
    }

    public function getPrunedTranslatableKeys(): Collection
    {
        return $this->translatable
            ->filter(fn ($key) => is_array($key) ? ! is_null($this->{key($key)}) : ! is_null($this->{$key}));
    }

    public function translateViaDictionary(string $targetLang): Collection
    {
        return $this->getPrunedTranslatableKeys()
            ->map(
                fn ($key) => config('deep-translatable.use_queue') ?
                Translate::dispatch(
                    $this,
                    $key,
                    $targetLang,
                    'languages',
                )
                : (new StoreTranslationAction(
                    $this,
                    $key,
                    $targetLang,
                    'languages',
                ))()
            );
    }

    public function getTranslationKeyByLang(string $key, string $lang): mixed
    {
        $accessor = "{$this->translationAccessor}_{$key}";

        return $this->{$accessor}[$lang] ?? null;
    }

    private static function verifyProperties(): void
    {
        if (! property_exists(self::class, 'translatable')) {
            throw new Exception('The translatable property must be declared');
        }
    }
}
