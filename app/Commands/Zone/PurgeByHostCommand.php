<?php

namespace App\Commands\Zone;

use App\Traits\ZonesTrait;
use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;

class PurgeByHostCommand extends Command
{
    use CommonTrait;
    use ZonesTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'zone:purge-host
                           {domain : The host name.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Remove ALL files from Cloudflare\'s cache, for a specific host';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\Zones $zones
     *
     * @throws \Cloudflare\API\Endpoints\EndpointException
     *
     * @return mixed
     */
    public function handle(Zones $zones)
    {
        try {
            $zoneId = $zones->getZoneID($this->domain);
            $status = $zones->cachePurge($zoneId, null, null, [$this->domain]);

            $this->task(sprintf('Cache purge for %s ', $this->domain), function () use ($status) {
                return $status;
            });
        } catch (EndpointException $exception) {
            $this->fail('Could not find zones with specified name.');
        } catch (ClientException $exception) {
            ClientException($exception)->each(function ($message): void {
                $this->fail($message);
            });
        }
    }
}
