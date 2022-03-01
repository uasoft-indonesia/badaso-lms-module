<?php

namespace Uasoft\Badaso\Module\LMSModule\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Uasoft\Badaso\Module\LMS\BadasoLMSModule;
use Uasoft\Badaso\Module\LMS\Commands\BadasoLMSSetup;
use Uasoft\Badaso\Module\LMS\Facades\BadasoLMSModule as FacadesBadasoLMS;

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

        $this->app->singleton('badaso-lms-module', function() {
            return new BadasoLMSModule();
        });

        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');

        $this->publishes([
            __DIR__.'Config/badaso-lms.php' => config_path('badaso-lms.php'),
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
        $this->commands(BadasoLMSSetup::class);
    }
}
