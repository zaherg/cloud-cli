<?php

namespace App\Commands\DNS;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use LaravelZero\Framework\Commands\Command;
use Cloudflare\API\Endpoints\EndpointException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListRecordsCommand extends Command
{
    use CommonTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:list-records
                            {--type= : Record type}
                            {--name= : Record name}
                            {--content= : Record content}
                            {domain : The domain name}';

    protected $name;
    protected $type;
    protected $content;

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all the DNS records for a specific domain.';

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->name = strtolower($this->option('name')) ?? '';
        $this->type = strtoupper($this->option('type')) ?? '';
        $this->content = $this->option('content') ?? '';
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
        $this->output->title('List all zone records');
        try {
            $zoneID = $zones->getZoneID($this->argument('domain'));
            $data = collect($dns->listRecords($zoneID, $this->type, $this->name, $this->content)->result)
                ->map(function ($record) {
                    return [
                        $record->type,
                        $record->name,
                        $record->content,
                        $this->isActive($record->proxied),
                    ];
                });

            if(count($data) > 0) {
                $this->table(['Type', 'Name', 'Content', 'Proxied'], $data);
            } else {
                $this->fail('Sorry, we couldn\'t find anything to display');
            }

        } catch (EndpointException $exception) {
            $this->output->error('Could not find zones with specified name.');
        }
    }
}
