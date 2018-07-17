<?php

namespace Addons\Elasticsearch\Scout;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class MakeSearchable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The models to be made searchable.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $models;
    public $refresh;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Database\Eloquent\Collection  $models
     * @return void
     */
    public function __construct($models, bool $refresh = true)
    {
        $this->models = $models;
        $this->refresh = $refresh;
    }

    /**
     * Handle the job.
     *
     * @return void
     */
    public function handle()
    {
        $t = microtime(true);
        if (count($this->models) === 0) {
            return;
        }

        $this->models->loadMissing($this->models->first()->searchableWith());

        echo PHP_EOL,(microtime(true) - $t), PHP_EOL;

        $this->models->first()->searchableUsing()->update($this->models, $this->refresh);
        echo (microtime(true) - $t), PHP_EOL;

    }
}
