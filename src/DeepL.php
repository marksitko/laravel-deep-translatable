<?php

namespace MarkSitko\DeepTranslatable;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use MarkSitko\DeepTranslatable\Lang;
use MarkSitko\DeepTranslatable\Contracts\Translatable;

class DeepL
{
    private $url = '';

    /**
     * Creates a new instance of DeepL.
     */
    public function __construct(
        public string $text = '',
        public string $targetLang = '',
        protected array $options = []
    ) {
        if (! Lang::exists($this->targetLang)) {
            throw new Exception("Invalid target language attribute: {$this->targetLang}, please refer to https://www.deepl.com/de/docs-api/other-functions/listing-supported-languages/");
        }

        if (isset($this->options['source_lang']) && ! Lang::exists($this->options['source_lang'])) {
            throw new Exception("Invalid source language attribute: {$this->options['source_lang']}, please refer to https://www.deepl.com/de/docs-api/other-functions/listing-supported-languages/");
        }

        if (! isset($this->options['source_lang'])) {
            $this->options['source_lang'] = config('deep-translatable.source_lang');
        }

        $this->options = array_merge($this->options, $this->getDefaultOptions());

        $this->url = config('deep-translatable.use_pro_version')
        ? 'https://api.deepl.com/v2/translate'
        : 'https://api-free.deepl.com/v2/translate';
    }

    public static function translate(string $text, string $targetLang, array $options = []): string | null
    {
        return (new self($text, $targetLang, $options))->getTranslationFromRequest();
    }

    public static function translateAndStore(string $text, string $targetLang, array $options = []): string | null
    {
        $instance = new self($text, $targetLang, $options);

        $dictionary = config('deep-translatable.translation_dictionary_model')::firstOrCreate([
            'key' => $text,
        ]);
        if (! $dictionary->hasTranslation($targetLang)) {
            $translation = $instance->getTranslationFromRequest();

            if (! is_null($translation)) {
                $dictionary->update([
                    'translations' => array_merge($dictionary->translations ?? [], [$targetLang => $translation]),
                ]);
            }

            return $translation;
        }

        return $dictionary->getTranslation($targetLang);
    }

    public static function translateNested(Translatable $translatable, array $nestedObject, string $targetLang, string $method): Collection
    {
        if (! in_array($method, ['translate', 'translateAndStore'])) {
            throw new Exception("Invalid provided method '{$method}'. Acceptet methods are 'translate' and 'translateAndStore'.");
        }
        $key = key($nestedObject);
        $data = clone $translatable->{$key};

        collect(data_get($data, $nestedObject[$key]))
            ->each(fn ($value, $accessor) => data_set($data, $nestedObject[$key].'.'.$accessor, self::$method($value, $targetLang)));

        return collect([
            'key' => $key,
            'data' => $data,
        ]);
    }

    public function request(): Response
    {
        try {
            $response = Http::get($this->url, $this->options);

            $response->throw();
        } catch (\Throwable $th) {
            throw $th;
        }

        return $response;
    }

    public function getTranslationFromRequest(): string | null
    {
        return $this->request()->json('translations')[0]['text'] ?? null;
    }

    protected function getDefaultOptions(): array
    {
        return [
            'auth_key' => config('deep-translatable.auth_key'),
            'text' => $this->text,
            'target_lang' => $this->targetLang,
        ];
    }
}
