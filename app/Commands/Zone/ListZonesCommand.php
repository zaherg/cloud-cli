<?php

namespace App\Commands\Zone;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class ListZonesCommand extends Command
{
    use CommonTrait;
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'zone:list';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\Zones $zones
     * @return mixed
     */
    public function handle(Zones $zones)
    {
        $header = ['Name', 'Status', 'Plan', 'Dev mode', 'Created on', 'Modified on'];
        $data = $this->getZones($zones);

        $this->output->section('List of all the Zones you have:');

        $this->table($header, $data);

    }

    /**
     * @param \Cloudflare\API\Endpoints\Zones $zones
     * @return \Illuminate\Support\Collection
     */
    private function getZones(Zones $zones)
    {
        return collect($zones->listZones()->result)
            ->map(function ($zone) {
                return [
                    $zone->name,
                    $zone->status,
                    $zone->plan->name,
                    $this->isActive($zone->development_mode),
                    $this->getDate($zone->created_on),
                    $this->getDate($zone->modified_on),
                ];
            });
    }
}
