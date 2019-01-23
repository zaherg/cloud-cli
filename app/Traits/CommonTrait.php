<?php

namespace App\Traits;

use Carbon\Carbon;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

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

    /**
     * Write a string as warning output.
     *
     * @param string          $string
     * @param int|string|null $verbosity
     */
    public function fail($string, $verbosity = null): void
    {
        if (! $this->output->getFormatter()->hasStyle('fail')) {
            $style = new OutputFormatterStyle('red');

            $this->output->getFormatter()->setStyle('fail', $style);
        }

        $this->line('[Error] ' . $string, 'fail', $verbosity);
    }
}
