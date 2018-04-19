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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class MakeSeederCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'entrust:seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the seeder following the Addons\Entrust specifications.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->laravel->view->addNamespace('entrust', __DIR__.'/../../resources/views');

        if (file_exists($this->seederPath())) {
            $this->line('');

            $this->warn("The EntrustSeeder file already exists. Delete the existing one if you want to create a new one.");
            $this->line('');
            return;
        }

        if ($this->createSeeder()) {
            $this->info("Seeder successfully created!");
        } else {
            $this->error(
                "Couldn't create seeder.\n".
                "Check the write permissions within the database/seeds directory."
            );
        }

        $this->line('');
    }

    /**
     * Create the seeder
     *
     * @return bool
     */
    protected function createSeeder()
    {
        $permission = Config::get('entrust.models.permission', 'App\Permission');
        $role = Config::get('entrust.models.role', 'App\Role');
        $rolePermissions = Config::get('entrust.tables.permission_role');
        $roleUsers = Config::get('entrust.tables.role_user');
        $user = new Collection(Config::get('entrust.user_models', ['App\User']));
        $user = $user->first();

        $output = $this->laravel->view->make('entrust::seeder')
            ->with(compact([
                'role',
                'permission',
                'user',
                'rolePermissions',
                'roleUsers',
            ]))
            ->render();

        if ($fs = fopen($this->seederPath(), 'x')) {
            fwrite($fs, $output);
            fclose($fs);
            return true;
        }

        return false;
    }

    /**
     * Get the seeder path.
     *
     * @return string
     */
    protected function seederPath()
    {
        return database_path("seeds/EntrustSeeder.php");
    }
}
