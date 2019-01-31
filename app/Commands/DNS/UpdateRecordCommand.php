<?php

namespace App\Commands\DNS;

use App\Traits\DNSTrait;
use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use App\Exceptions\RecordNotFoundException;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRecordCommand extends Command
{
    use DNSTrait;
    use CommonTrait;

    protected $data;
    protected $record;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:update
                            {--type= : Record type, valid values: A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF, CERT, DNSKEY, DS, NAPTR, SMIMEA, SSHFP, TLSA, URI}
                            {--name= : Record name, max length: 255}
                            {--new-name= : The new record name, max length: 255}
                            {--content= : Record content}
                            {--optional : Whether we should ask for the none required values}
                            {--change-name : Whether you want to change the old name or not, if enabled you will be asked for a new name}
                            {--proxied : Page number of paginated results, default: false}
                            {--ttl= : Time to live for DNS record, default: 120, min: 120, max: 2147483647}
                            {--priority= : Used with some records like MX and SRV to determine priority, default: 10, min: 0, max: 65535}
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update DNS Record';

    /**
     * The Zones instance.
     *
     * @var \Cloudflare\API\Endpoints\Zones
     */
    private $zones;

    /**
     * The DNS instance.
     *
     * @var \Cloudflare\API\Endpoints\DNS
     */
    private $dns;

    public function __construct(DNS $dns, Zones $zones)
    {
        parent::__construct();

        $this->dns = $dns;
        $this->zones = $zones;
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
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->output->title($this->description);

        $this->showIntro();

        $this->setDomainArgument();

        $this->setRecordName();

        // check for the existence of the record before we continue
        $this->record = $this->existedDnsRecord();

        if ($this->option('change-name')) {
            $this->setNewRecordName();
        }

        $this->setRecordType();
        $this->setRecordContent();

        $this->data = [
            'name' => $this->option('change-name') ? $this->newRecordName : $this->recordName,
            'type' => $this->recordType,
            'content' => $this->recordContent,
        ];

        if ($this->option('optional')) {
            $this->setRecordTtl();
            $this->setRecordProxiedStatus();

            $this->data = array_merge($this->data, [
                'ttl' => $this->recordTtl,
                'proxied' => $this->proxied,
            ]);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $zoneID = $this->zones->getZoneID($this->domain);

            $status = $this->dns->updateRecordDetails($zoneID, $this->record->id, $this->data);

            $status->success ? $this->info(sprintf(
                'The record %s has been updated within DNS Zone : %s .',
                $this->data['name'],
                $this->domain
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


    private function existedDnsRecord(): \stdClass
    {
        $record = $this->getRecordId();

        if (null === $record) {
            throw new RecordNotFoundException('Sorry, we couldn\'t find the record you asked for.');
        }

        return $record;
    }

    /**
     * @return mixed
     */
    private function getRecordId()
    {
        try {
            $zoneID = $this->zones->getZoneID($this->domain);
            $name = $this->domain !== $this->recordName ? $this->recordName . '.' . $this->domain : $this->domain;

            return collect($this->dns->listRecords($zoneID, '', $name)->result)->first();
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        }
    }

    private function setNewRecordName(): void
    {
        while (! filter_var($this->newRecordName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || strlen($this->newRecordName) > 255) {
            $this->newRecordName = strtolower($this->ask('DNS record new name. Valid values can be : example.com, mail.example.com', $this->recordName));
        }
    }
}
