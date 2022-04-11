<?php

namespace MarkSitko\DeepTranslatable\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class TranslationsScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($model->shouldUseOtherLanguage()) {
            $model->getTranslatableKeys()
                ->each(
                    function ($key) use ($builder, $model) {
                        $key = is_array($key) ? key($key) : $key;
                        $builder->addSelect([
                            "{$model->translationAccessor}_{$key}" => config('deep-translatable.translation_model')::select('languages')
                                ->whereColumn('translatable_id', "{$model->getTable()}.{$model->getKeyName()}")
                                ->where('translatable_type', $model::class)
                                ->where('key', $key)
                                ->take(1),
                        ])->withCasts(["{$model->translationAccessor}_{$key}" => 'array']);
                    }
                );
        }
    }
}
