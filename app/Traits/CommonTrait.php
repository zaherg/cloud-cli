<?php

namespace App\Traits;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait CommonTrait
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // TODO: check for the .env file on every command.
    }

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
