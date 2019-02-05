<?php

namespace App\Commands\DNS;

use App\Traits\DNSTrait;
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
    use DNSTrait;
    use CommonTrait;

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
                            {--ttl= : Time to live for DNS record, default: 120, min: 120, max: 2147483647}
                            {--priority= : Used with some records like MX and SRV to determine priority, default: 10, min: 0, max: 65535}
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new DNS record for a zone';

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
            $zoneID = $zones->getZoneID($this->domain);

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
                $this->domain
            ))
                : $this->fail('Sorry, something went wrong and we couldn\'t add the new record to your DNS Zone.');
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        } catch (ClientException $exception) {
            $errors = collect(json_decode((string) $exception->getResponse()->getBody())->errors);

            $errors->each(function ($error): void {
                $this->fail($error->message);
            });
        }
    }
}
