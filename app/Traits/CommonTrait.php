<?php

namespace App\Traits;

use Carbon\Carbon;


trait CommonTrait
{
    protected function getDate($date): string
    {
        return Carbon::createFromTimestamp(strtotime($date))
            ->toDayDateTimeString();
    }


    protected function isActive($value): string
    {
        return $value ? 'Yes' : 'No';
    }
}
