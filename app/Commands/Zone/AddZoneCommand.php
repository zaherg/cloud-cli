<?php

namespace App\Commands\Zone;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddZoneCommand extends Command
{
    use CommonTrait;
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'zone:add
                           {domain : The domain name that you need, max length: 253.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create a new Zone';

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
        $this->setDomainArgument();
    }

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\Zones $zones
     *
     * @return mixed
     */
    public function handle(Zones $zones)
    {
        $this->checkDomainName($this->domain);

        try {
            $zones->addZone($this->domain);

            $this->info(sprintf(
                    'Domain %s has been added successfully, please remember to update the DNS records',
                    $this->domain
                ));
        } catch (ClientException $exception) {
            ClientException($exception)->each(function ($message): void {
                $this->fail($message);
            });
        }
    }
}
