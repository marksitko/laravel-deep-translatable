<?php

namespace MarkSitko\DeepTranslatable\Contracts;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

interface Translatable
{
    public function translations() : MorphOneOrMany;

    public function translate(string $targetLang): mixed;

    public function translateViaDictionary(string $targetLang): Collection;

    public function getTranslatableKeys(): Collection;
}
