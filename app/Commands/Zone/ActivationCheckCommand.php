<?php

namespace App\Commands\Zone;

use App\Traits\ZonesTrait;
use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;

class ActivationCheckCommand extends Command
{
    use CommonTrait;
    use ZonesTrait;

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
            ClientException($exception)->each(function ($message): void {
                $this->fail($message);
            });
        }
    }
}
