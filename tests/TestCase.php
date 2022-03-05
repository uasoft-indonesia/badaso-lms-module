<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests;

use Uasoft\Badaso\Module\LMSModule\Providers\LMSModuleProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $loadEnvironmentVariables = true;

    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            LMSModuleProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('badaso.database.prefix', 'badaso_');
        $app['config']->set('database.default', 'testing');
    }
}
