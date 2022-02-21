<?php

namespace Uasoft\Badaso\Module\LMS\Commands;

use Illuminate\Console\Command;

class BadasoLMSSetup extends Command
{
    protected $file;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'badaso-lms:setup';

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
    public function __construct()
    {
        $this->file = app('files');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->addingBadasoEnv();
        $this->publishBadasoProvider();
        $this->addPostTablesToHiddenTables();
        $this->linkStorage();
        $this->generateSwagger();
    }

    protected function generateSwagger()
    {
    }

    protected function publishBadasoProvider()
    {
    }

    protected function linkStorage()
    {
    }

    protected function envListUpload()
    {
    }

    protected function addingBadasoEnv()
    {
    }

    protected function addPostTablesToHiddenTables()
    {
    }
}
