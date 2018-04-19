<?php

namespace Addons\Entrust\Commands;

/**
 * This file is part of Addons\Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Addons\Entrust
 */
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class SetupCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'entrust:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup migration and models for Addons\Entrust';

    /**
     * Commands to call with their description.
     *
     * @var array
     */
    protected $calls = [
        'entrust:migration' => 'Creating migration',
        'entrust:role' => 'Creating Role model',
        'entrust:permission' => 'Creating Permission model',
    ];

    /**
     * Create a new command instance
     *
     * @return void
     */
    public function __construct()
    {
        if (Config::get('entrust.use_teams')) {
            $this->calls['entrust:team'] = 'Creating Team model';
        }

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->calls as $command => $info) {
            $this->line(PHP_EOL . $info);
            $this->call($command);
        }
    }
}
