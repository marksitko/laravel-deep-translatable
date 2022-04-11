<?php

namespace MarkSitko\DeepTranslatable\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'key',
        'languages',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'languages' => 'array',
    ];

    /**
     * Get the parent translatable model.
     */
    public function translatable()
    {
        return $this->morphTo();
    }
}
