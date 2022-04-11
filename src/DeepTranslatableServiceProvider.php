<?php

namespace MarkSitko\DeepTranslatable;

use Illuminate\Support\ServiceProvider;

class DeepTranslatableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('deep-translatable.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations/create_translation_dictionaries_table.php.stub' => database_path('migrations').'/'.date('Y_m_d_His').'_create_translation_dictionaries_table.php',
            ], 'migrations');
            $this->publishes([
                __DIR__.'/../database/migrations/create_translations_table.php.stub' => database_path('migrations').'/'.date('Y_m_d_His').'_create_translations_table.php',
            ], 'migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'deep-translatable');
    }
}
