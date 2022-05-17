<?php

namespace Uasoft\Badaso\Module\LMSModule\Traits;

use DateTimeInterface;

trait Iso8601Serialization
{
    public function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d\TH:i:sp');
    }
}
