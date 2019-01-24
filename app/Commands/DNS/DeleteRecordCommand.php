<?php

namespace App\Commands\DNS;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use App\Exceptions\RecordNotFoundException;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteRecordCommand extends Command
{
    use CommonTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:delete
                            {--name= : The name of the record you want to delete, example: test, admin, portal,mail .. etc}
                            {domain : The zone domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete DNS Record';

    /**
     * The name of the record you want to delete.
     *
     * @var string
     */
    private $recordName;

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

        $this->recordName = $this->option('name');
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
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->output->title($this->description);

        $this->setDomainArgument();

        $this->setNameOption();
    }

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\Zones $zones
     * @param \Cloudflare\API\Endpoints\DNS   $dns
     *
     * @return mixed
     */
    public function handle(Zones $zones, DNS $dns)
    {
        try {
            $zoneID = $zones->getZoneID($this->domain);

            $name = $this->recordName . '.' . $this->domain;

            $record = collect($dns->listRecords($zoneID, '', $name)->result)->first();

            if (null === $record) {
                throw new RecordNotFoundException('Sorry, we couldn\'t find the record you asked for.');
            }

            $confirm = $this->confirm(sprintf('Are you sure you want to delete the record %s ?', $record->name));

            if ($confirm) {
                $status = $dns->deleteRecord($zoneID, $record->id);

                $status ? $this->info(sprintf(
                    'The record %s has been deleted from your DNS Zone : %s .',
                    $this->recordName,
                    $this->domain
                ))
                    : $this->fail('Sorry, something went wrong and we couldn\'t delete the record from your DNS Zone.');
            } else {
                $this->comment('Nothing has changed.');
            }
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        } catch (ClientException $exception) {
            $errors = collect(json_decode((string) $exception->getResponse()->getBody())->errors);

            $errors->each(function ($error) {
                $this->fail($error->message);
            });
        } catch (RecordNotFoundException $exception) {
            $this->fail($exception->getMessage());
        }
    }

    private function setNameOption(): void
    {
        while (null === $this->option('name') && empty($this->recordName)) {
            $this->recordName = $this->ask('What is the name of the record you want to delete');
        }
    }
}
