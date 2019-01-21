<?php

namespace App\Commands\Zone;

use Cloudflare\API\Endpoints\Zones;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class PurgeAllCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'zone:purge-all';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Purge Cache on Every Website';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\Zones $zones
     *
     * @return mixed
     */
    public function handle(Zones $zones)
    {
        if (! $this->output->getFormatter()->hasStyle('fail')) {
            $style = new OutputFormatterStyle('red');

            $this->output->getFormatter()->setStyle('fail', $style);
        }

        $this->output->title('Purge Cache on Every Website:');

        collect($zones->listZones()->result)
            ->each(function ($zone) use ($zones) {
                try {
                    $status = $zones->cachePurgeEverything($zone->id) === true ? '<info>successful</info>' : '<fail>failed</fail>';
                } catch (\GuzzleHttp\Exception\ServerException $exception) {
                    $status = '<fail>failed</fail>';
                }

                $this->output->writeln(sprintf('Cache purge for %s : %s', $zone->name, $status));
            });
    }
}
