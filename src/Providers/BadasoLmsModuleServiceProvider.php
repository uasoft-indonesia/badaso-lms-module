<?php

namespace Uasoft\Badaso\Module\Lms\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Uasoft\Badaso\Module\Lms\BadasoLmsModule;
use Uasoft\Badaso\Module\Lms\Commands\BadasoLmsSetup;
use Uasoft\Badaso\Module\Lms\Facades\BadasoLmsModule as FacadesBadasoLmsModule;

class BadasoLmsModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('BadasoLmsModule', FacadesBadasoLmsModule::class);

        $this->app->singleton('badaso-lms-module', function () {
            return new BadasoLmsModule();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'badaso-lms');

        $this->publishes([
            __DIR__ . '/../Swagger' => app_path('Http/Swagger/swagger_models'),
            __DIR__ . '/../Seeder' => database_path('seeders/Badaso/Lms'),
            __DIR__ . '/../Config/access.php' => config_path('access.php'),
            __DIR__ . '/../Config/chatmessenger.php' => config_path('chatmessenger.php'),
            __DIR__ . '/../Config/menu.php' => config_path('menu.php'),
            __DIR__ . '/../Config/permission.php' => config_path('permission.php'),
        ], 'badaso-lms-module');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConsoleCommands();
    }

    /**
     * Register the commands accessible from the Console.
     */
    private function registerConsoleCommands()
    {
        $this->commands(BadasoLmsSetup::class);
    }
}
