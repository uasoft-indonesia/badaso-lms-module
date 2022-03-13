<?php

namespace Uasoft\Badaso\Module\LMSModule\Seeders;

use Illuminate\Database\Seeder;
use Uasoft\Badaso\Models\Permission;

class LMSPermissionsSeeder extends Seeder
{
    public function run()
    {
        Permission::generateFor('courses');
        Permission::generateFor('auth');
    }
}
