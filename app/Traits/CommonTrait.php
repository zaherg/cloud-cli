<?php

namespace App\Traits;

use Carbon\Carbon;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

trait CommonTrait
{
    protected $domain;

    protected function formatDate($date): string
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

    protected function setDomainArgument(): void
    {
        if (null !== $this->argument('domain')) {
            $this->domain = $this->argument('domain');
        }

        while (null === $this->argument('domain') && empty($this->domain)) {
            $this->domain = trim($this->ask('What is the domain name for the zone'));
            $this->input->setArgument('domain', $this->domain);
        }
    }

    protected function setAlwaysInteract(): void
    {
        if ($this->option('no-interaction')) {
            $this->input->setInteractive(true);
        }
    }
}
