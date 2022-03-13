<?php

namespace Uasoft\Badaso\Module\LMSModule\Seeders;

use Illuminate\Database\Seeder;

class BadasoLMSModuleSeeder extends Seeder
{
    public function run()
    {
        $this->call(LMSPermissionsSeeder::class);
        $this->call(LMSUserSeeder::class);
    }
}
