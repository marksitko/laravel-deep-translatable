<?php

namespace MarkSitko\DeepTranslatable\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MarkSitko\DeepTranslatable\Contracts\Translatable;
use MarkSitko\DeepTranslatable\Actions\StoreTranslationAction;

class Translate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Translatable $translatable,
        public string | array $key,
        public string $locale,
        public string $column,
        public array $where = []
    ) {
        if (! is_null($queue = config('deep-translatable.queue_name'))) {
            $this->onQueue($queue);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new StoreTranslationAction(
            $this->translatable,
            $this->key,
            $this->locale,
            $this->column,
            $this->where
        ))();
    }
}
