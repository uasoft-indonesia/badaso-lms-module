---
sidebar_position: 1
---

# Installation

## Module

1. [Install badaso](https://badaso-docs.uatech.co.id/getting-started/installation) first.
2. Make sure you already run the migration and badaso seeder, even if you're using docker.
3. Run the following command to install badaso LMS module
   ```bash
   # as of now, because all of our works are still in the staging branch, you have to add the `:dev-staging`
   composer require badaso/lms-module:dev-staging
   ```
4. Run the following commands to finish the badaso LMS module setup
   ```bash
   php artisan migrate
   php artisan badaso-lms-module:setup
   composer dump-autoload
   ```
5. Make sure that all of the routes declared by badaso LMS module are already accessible. You can see the defined routes by running the following command
   ```bash
   php artisan route:list
   ```
    If you see something similar to this line `POST badaso-api/module/lms/v1/auth/login`, that means you have successfully set up your badaso application.

<!-- For badaso v2.x (Laravel 8)
```
php artisan db:seed --class="Database\Seeders\Badaso\LMS\BadasoLMSModuleSeeder"
```

For badaso v1.x (Laravel 5, 6, 7)
```
php artisan db:seed --class=BadasoLMSModuleSeeder
``` -->

That's all, and you should be good to go!

## Theme

Badaso LMS also comes with a free-to-use open-source theme, called badaso-ilma-theme. You are free to use this theme, or you can also build your own theme to your liking. To use badaso-ilma-theme, do follow the following steps.

1. Make sure you alerady have [badaso](https://github.com/uasoft-indonesia/badaso) and [badaso-lms-module](https://github.com/uasoft-indonesia/badaso-lms-module) installed.
2. Install badaso-ilma-theme using the following command.
   ```bash
   composer require badaso/ilma-theme
   ```
3. Update the dependency by running
   ```bash
   npm install
   ```
3. Run the following command to setup the `badaso-content`.
   ```bash
   php artisan badaso-content:setup
   ```
4. Run the following command to migrate `badaso-content` table.
   ```bash
   php artisan migrate
   ```

5. Run the following command.
   ```bash
   php artisan badaso-lms-theme:setup
   ```
