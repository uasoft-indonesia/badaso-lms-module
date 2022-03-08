<?php

namespace Uasoft\Badaso\Module\LMSModule\Tests;

use Uasoft\Badaso\Module\LMSModule\Providers\LMSModuleProvider;
use \Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
  public function setUp(): void
  {
    parent::setUp();
  }

  protected function getPackageProviders($app)
  {
    return [
      LMSModuleProvider::class,
    ];
  }
}