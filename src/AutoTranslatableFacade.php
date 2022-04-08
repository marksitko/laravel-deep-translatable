<?php

namespace MarkSitko\AutoTranslatable;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MarkSitko\AutoTranslatable\Skeleton\SkeletonClass
 */
class AutoTranslatableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'auto-translatable';
    }
}
