<?php

namespace Uasoft\Badaso\Module\LMSModule\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\VarExporter\VarExporter;
use Uasoft\Badaso\Module\LMSModule\Facades\LMSModule;

class LMSModuleSetup extends Command {
    protected $file;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'badaso-lms-module:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Badaso Modules For LMS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        $this->file = app('files');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle() {
        $this->addBadasoEnv();
        $this->publishBadasoProvider();
        $this->addLMSTablesToHiddenTables();
        $this->linkStorage();
    }

    protected function publishBadasoProvider() {
        Artisan::call('vendor:publish', ['--tag' => 'BadasoLMSModule']);

        $this->info('Badaso LMS provider published');
    }

    protected function linkStorage() {
        Artisan::call('storage:link');
    }

    protected function envListUpload() {
        return [
            'MIX_LMS_URL_PREFIX' => '/lms',
            'MIX_FRONTEND_URL' => 'http://localhost:8000',
        ];
    }

    protected function addBadasoEnv() {
        try {
            $env_path = base_path('.env');

            $env_file = file_get_contents($env_path);
            $arr_env_file = explode("\n", $env_file);

            $env_will_adding = $this->envListUpload();

            $new_env_adding = [];
            foreach ($env_will_adding as $key_add_env => $val_add_env) {
                $status_adding = true;
                foreach ($arr_env_file as $val_env_file) {
                    $val_env_file = trim($val_env_file);
                    if (substr($val_env_file, 0, 1) != '#' && $val_env_file != '' && strstr($val_env_file, $key_add_env)) {
                        $status_adding = false;
                        break;
                    }
                }
                if ($status_adding) {
                    $new_env_adding[] = "{$key_add_env}={$val_add_env}";
                }
            }

            foreach ($new_env_adding as $val_env_add) {
                $arr_env_file[] = $val_env_add;
            }

            $env_file = join("\n", $arr_env_file);
            file_put_contents($env_path, $env_file);

            $this->info('Adding badaso env');
        } catch (\Exception $e) {
            $this->error('Failed adding badaso env '.$e->getMessage());
        }
    }

    protected function addLMSTablesToHiddenTables() {
        try {
            $config_path = config_path('badaso-hidden-tables.php');
            $config_hidden_tables = require $config_path;
            $tables = LMSModule::getProtectedTables();

            foreach ($tables as $value) {
                if (! in_array($value, $config_hidden_tables)) {
                    array_push($config_hidden_tables, $value);
                }
            }

            $exported_config = VarExporter::export($config_hidden_tables);
            $exported_config = <<<PHP
                <?php
                return {$exported_config} ;
                PHP;
            file_put_contents($config_path, $exported_config);
            $this->info('Adding badaso hidden tables config');
        } catch (\Exception $e) {
            $this->error('Failed adding badaso hidden tables config ', $e->getMessage());
        }
    }
}
