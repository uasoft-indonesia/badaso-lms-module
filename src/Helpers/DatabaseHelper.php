<?php

namespace Uasoft\Badaso\Module\LMSModule\Helpers;

class DatabaseHelper
{
    public static function getBadasoTableName($tableName)
    {
        return config('badaso.database.prefix').$tableName;
    }
}
