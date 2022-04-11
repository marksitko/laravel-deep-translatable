<?php

namespace MarkSitko\DeepTranslatable\Actions;

use MarkSitko\DeepTranslatable\DeepL;
use MarkSitko\DeepTranslatable\Contracts\Translatable;

class StoreTranslationAction
{
    /**
     * Creates a new instance of StoreTranslationAction.
     */
    public function __construct(
        public Translatable $translatable,
        public string | array $key,
        public string $locale,
        public string $column,
        public array $where = []
    ) {
    }

    public function __invoke(): Translatable | null
    {
        if (is_array($this->key)) {
            $result = DeepL::translateNested(
                $this->translatable,
                $this->key,
                $this->locale,
                'translateAndStore'
            );

            return $this->store((string) $result->get('key'), $result->get('data'));
        } else {
            return $this->store($this->key, DeepL::translateAndStore($this->translatable->{$this->key}, $this->locale, [
                'tag_handling' => 'xml',
                'ignore_tags' => 'non-translatable',
            ]));
        }
    }

    protected function store(string $key, mixed $data): Translatable | null
    {
        if (is_null($data)) {
            return null;
        }
        $this->translatable->translations()->updateOrCreate(
            array_merge(['key' => $key], $this->where),
            [
                $this->column => array_merge(
                    $this->translatable->translations()->where('key', $key)->first()?->{$this->column} ?? [],
                    [$this->locale => $data]
                ),
            ]
        );

        return $this->translatable;
    }
}
