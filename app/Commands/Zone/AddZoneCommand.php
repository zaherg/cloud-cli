<?php

namespace App\Commands\Zone;

use App\Traits\ZonesTrait;
use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\Zones;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;

class AddZoneCommand extends Command
{
    use CommonTrait;
    use ZonesTrait;

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
