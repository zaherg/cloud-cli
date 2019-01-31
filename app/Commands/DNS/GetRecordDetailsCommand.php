<?php

namespace App\Commands\DNS;

use App\Traits\DNSTrait;
use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use App\Exceptions\RecordNotFoundException;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetRecordDetailsCommand extends Command
{
    use DNSTrait;
    use CommonTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:details                            
                            {--name= : Record name, max length: 255}
                            {--json : return the information as json}
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'DNS Record Details';

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

            $name = ($this->recordName !== $this->domain) ? $this->recordName . '.' . $this->domain : '';

            $record = collect($dns->listRecords($zoneID, '', $name)->result)->first();

            if (null === $record) {
                throw new RecordNotFoundException('Sorry, we couldn\'t find the record you asked for.');
            }

            if ($this->option('json')) {
                $this->line(collect($record)->toJson());
                exit(0);
            }

            $this->output->writeln(sprintf('<info>Record ID</info>: %s', $record->id));
            $this->output->writeln(sprintf('<info>Record type</info>: %s', $record->type));
            $this->output->writeln(sprintf('<info>Record name</info>: %s', $record->name));
            $this->output->writeln(sprintf('<info>Record zone name</info>: %s', $record->zone_name));
            $this->output->writeln(sprintf('<info>Record content</info>: %s', $record->content));
            $this->output->writeln(sprintf('<info>Record Time to live</info>: %s', $record->ttl));
            $this->output->writeln(sprintf('<info>Is proxiable?</info> %s', $this->isActive($record->proxiable)));
            $this->output->writeln(sprintf('<info>Is proxied?</info> %s', $this->isActive($record->proxied)));
            $this->output->writeln(sprintf('<info>Is locked?</info> %s', $this->isActive($record->locked)));
            $this->output->writeln(sprintf('<info>Created at</info>: %s', $this->formatDate($record->created_on)));
            $this->output->writeln(sprintf('<info>Modified at</info>: %s', $this->formatDate($record->modified_on)));
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        }
    }
}
