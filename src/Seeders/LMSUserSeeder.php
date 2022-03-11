<?php

namespace Uasoft\Badaso\Module\LMSModule\Seeders;

use Illuminate\Database\Seeder;
use Uasoft\Badaso\Module\LMSModule\Models\User;

class LMSUserSeeder extends Seeder
{
    public function run()
    {
        User::factory()
            ->count(2)
            ->create();
    }
}
