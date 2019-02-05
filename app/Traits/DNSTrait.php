<?php

namespace App\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait DNSTrait
{
    protected $recordName = '';
    protected $recordContent = '';
    protected $recordType = '';
    protected $recordTtl = 120;
    protected $proxied = 'false';
    protected $priority = 10;
    protected $newRecordName = '';

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->setAlwaysInteract();

        $this->recordName = $this->option('name') ?? $this->recordName;

        if ($this->hasOption('new-name')) {
            $this->newRecordName = $this->option('new-name') ?? $this->newRecordName;
        }

        $this->recordType = strtoupper($this->option('type')) ?? $this->recordType;
        $this->recordContent = $this->option('content') ?? $this->recordContent;
        $this->recordTtl = (int) ($this->option('ttl') ?? $this->recordTtl);
        $this->proxied = $this->option('proxied');
        $this->priority = (int) ($this->option('priority') ?? $this->priority);
        $this->domain = $this->argument('domain');
    }

    private function showIntro(): void
    {
        $displayed = false;
        $message = 'Please answer the following questions:';

        if (in_array(null, array_only($this->options(), ['type', 'name', 'content']), true)) {
            $this->output->block($message);
            $displayed = true;
        }

        if ($this->hasOption('optional') && ! $displayed && $this->option('optional') && in_array(null, array_only($this->options(), ['ttl', 'proxied', 'priority']), true)) {
            $this->output->block($message);
        }
    }

    private function setRecordName(): void
    {
        while ((empty($this->recordName) && ! filter_var($this->recordName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) || (strlen($this->recordName) > 255)) {
            $this->recordName = strtolower($this->ask('DNS record name. Valid values can be : example.com, mail.example.com'));
        }
    }

    private function setRecordType(): void
    {
        $types = ['A', 'AAAA', 'CNAME', 'TXT', 'SRV', 'LOC', 'MX', 'NS', 'SPF', 'CERT', 'DNSKEY', 'DS', 'NAPTR', 'SMIMEA', 'SSHFP',
            'TLSA', 'URI', ];

        while (! in_array($this->recordType, $types, true)) {
            $this->recordType = strtoupper($this->askWithCompletion(implode([
                'The DNS record type. Valid values are: ',
                implode($types, ', '),
            ]), $types, 'A'));
        }
    }

    private function setRecordContent(): void
    {
        while (empty($this->recordContent)) {
            $this->recordContent = $this->ask('DNS record content');
        }
    }

    private function setRecordTtl(): void
    {
        if (null === $this->option('ttl')) {
            $this->recordTtl = $this->option('optional') ? 0 : $this->recordTtl;
        }

        while ($this->recordTtl < 120 || $this->recordTtl > 2147483647) {
            $this->recordTtl = (int) $this->ask('Time to live for DNS record (min: 120, max: 2147483647)', 120);
        }
    }

    private function setRecordPriority(): void
    {
        if (null === $this->option('priority')) {
            $this->priority = $this->option('optional') ? -1 : $this->priority;

            while ($this->priority < 0 || $this->priority > 65535) {
                $this->priority = $this->ask(implode([
                    'DNS record priority. Used with some records like MX and SRV to ',
                    'determine priority. If you do not supply a priority for an MX record, a default value of 0 will ',
                    'be set (min: 0, max: 65535)',
                ]));

                if (! empty($this->priority)) {
                    $this->priority = (int) $this->priority;
                }

                if (null === $this->priority && $this->recordType === 'MX') {
                    $this->priority = 0;
                } elseif (null === $this->priority) {
                    $this->priority = '';
                }
            }
        }
    }

    private function setRecordProxiedStatus(): void
    {
        if (! $this->option('proxied')) {
            $this->proxied = $this->choice('Should the record receive the performance and security benefits of Cloudflare?', ['false', 'true'], 0);
            $this->proxied = $this->proxied !== 'false';
        }
    }
}
