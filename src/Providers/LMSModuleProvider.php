<?php

namespace Uasoft\Badaso\Module\LMSModule\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Uasoft\Badaso\Module\LMSModule\LMSModule;
use Uasoft\Badaso\Module\LMSModule\Commands\LMSModuleSetup;
use Uasoft\Badaso\Module\LMSModule\Facades\LMSModule as FacadesBadasoLMS;

class LMSModuleProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('BadasoLMSModule', FacadesBadasoLMS::class);

        $this->app->singleton('lms-module', function() {
            return new LMSModule();
        });

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->publishes([
            __DIR__.'Config/lms-module.php' => config_path('lms-module.php'),
        ], 'BadasoLMSModule');
    }

    /**
     * Register any application services.
     *
     * @return void
     */

    public function register()
    {
        $this->registerConsoleComannds();
    }

    /**
     * Register the commands accesible from the console.
     *
     * @return void
     */
    private function registerConsoleComannds()
    {
        $this->commands(LMSModuleSetup::class);
    }
}
