<?php

namespace App\Commands\DNS;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddRecordCommand extends Command
{
    use CommonTrait;

    protected $recordName = '';
    protected $recordContent = '';
    protected $recordType = '';
    protected $recordTtl = 120;
    protected $proxied = 'false';
    protected $priority = 10;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:add
                            {--type= : Record type, valid values: A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF, CERT, DNSKEY, DS, NAPTR, SMIMEA, SSHFP, TLSA, URI}
                            {--name= : Record name, max length: 255}
                            {--content= : Record content}
                            {--optional : Whether we should ask for the none required values.}
                            {--proxied : Page number of paginated results, default: false}
                            {--ttl=120 : Time to live for DNS record, default: 120, min: 120, max: 2147483647}
                            {--priority=10 : Used with some records like MX and SRV to determine priority, default: 10, min: 0, max: 65535}
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new DNS record for a zone.';

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
        $this->recordType = strtoupper($this->option('type')) ?? $this->recordType;
        $this->recordContent = $this->option('content') ?? $this->recordContent;
        $this->recordTtl = (int) ($this->option('ttl') ?? $this->recordTtl);
        $this->proxied = $this->option('proxied');
        $this->priority = (int) ($this->option('priority') ?? $this->priority);
        $this->domain = $this->argument('domain');
    }

    /**
     * Interacts with the user.
     *
     * This method is executed before the InputDefinition is validated.
     * This means that this is the only place where the command can
     * interactively ask for values of missing required arguments.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->output->title('Create a new DNS record:');

        $this->showIntro();

        $this->setDomainArgument();

        $this->setRecordName();
        $this->setRecordType();
        $this->setRecordContent();

        if ($this->option('optional')) {
            $this->setRecordTtl();
            $this->setRecordPriority();
            $this->setRecordProxiedStatus();
        }
    }

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\DNS   $dns
     * @param \Cloudflare\API\Endpoints\Zones $zones
     *
     * @return mixed
     */
    public function handle(DNS $dns, Zones $zones)
    {
        try {
            $zoneID = $zones->getZoneID($this->argument('domain'));

            $status = $dns->addRecord(
                $zoneID,
                $this->recordType,
                $this->recordName,
                $this->recordContent,
                $this->recordTtl,
                $this->proxied,
                $this->priority
            );

            $status ? $this->info(sprintf(
                'A new record %s has been added to your DNS Zone : %s .',
                $this->recordName,
                $this->argument('domain')
            ))
                : $this->fail('Sorry, something went wrong and we couldn\'t add the new record to your DNS Zone.');
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        } catch (ClientException $exception) {
            $errors = collect(json_decode((string) $exception->getResponse()->getBody())->errors);

            $errors->each(function ($error) {
                $this->fail($error->message);
            });
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

            while ($this->recordTtl < 120 || $this->recordTtl > 2147483647) {
                $this->recordTtl = (int) $this->ask('Time to live for DNS record (min: 120, max: 2147483647)', 120);
            }
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

    private function showIntro()
    {
        $displayed = false;
        if (in_array(null, array_only($this->options(), ['type', 'name', 'content']), true)) {
            $this->output->block('Please answer the following questions:');
            $displayed = true;
        }

        if (! $displayed && $this->option('optional') && in_array(null, array_only($this->options(), ['ttl', 'proxied', 'priority']), true)) {
            $this->output->block('Please answer the following questions:');
        }
    }
}
