<?php

namespace App\Commands\DNS;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;

class ListRecordsCommand extends Command
{
    use CommonTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:list-records
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all the DNS records for a specific domain.';

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
        $this->output->title('List all zone records');
        try {
            $zoneID = $zones->getZoneID($this->argument('domain'));
            $data = collect($dns->listRecords($zoneID)->result)
                ->map(function ($record) {
                    return [
                        $record->type,
                        $record->name,
                        $record->content,
                        $this->isActive($record->proxied),
                    ];
                });

            $this->table(['Type', 'Name', 'Content', 'Proxied'], $data);
        } catch (EndpointException $exception) {
            $this->output->error('Could not find zones with specified name.');
        }
    }
}
