<?php

namespace App\Commands\Zone;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivationCheckCommand extends Command
{
    use CommonTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'zone:check-activation
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Initiate another zone activation check';

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
        try {
            $zoneId = $zones->getZoneID($this->domain);

            $zones->activationCheck($zoneId) ?
                $this->info('We have successfully initiated another zone activation check') :
                $this->fail('Something went wrong.');
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        } catch (ClientException $exception) {
            $errors = collect(json_decode((string) $exception->getResponse()->getBody())->errors);

            $errors->each(function ($error) {
                $this->fail($error->message);
            });
        }
    }
}
