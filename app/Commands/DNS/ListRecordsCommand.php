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
                            {--type= : Record type, valid values: A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF, CERT, DNSKEY, DS, NAPTR, SMIMEA, SSHFP, TLSA, URI}
                            {--name= : Record name}
                            {--content= : Record content}
                            {--limit= : Number of DNS records per page, default: 20, min :5, max :100}
                            {--page= : Page number of paginated results, default: 1, min :1}
                            {--order= : Field to order records by, valid values: type, name, content, ttl, proxied}
                            {--direction= : Direction to order domains, valid values: desc or asc}
                            {--match= : Whether to match all search requirements or at least one (any), valid values: any, all}
                            {domain : The domain name}';

    protected $name;
    protected $type;
    protected $content;
    protected $page = 1;
    protected $limit = 20;
    protected $order = 'type';
    protected $match = 'all';
    protected $direction = 'desc';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List all the DNS records for a specific domain.';

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->name = strtolower($this->option('name')) ?? '';
        $this->content = $this->option('content') ?? '';

        $this->setType();
        $this->setPage();
        $this->setLimit();
        $this->setOrder();
        $this->setMatch();
        $this->setDirection();
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

            $records = $dns->listRecords(
                $zoneID,
                $this->type,
                $this->name,
                $this->content,
                $this->page,
                $this->limit,
                $this->order,
                $this->direction,
                $this->match
            );

            $data = collect($records->result)
                ->map(function ($record) {
                    return [
                        $record->type,
                        $record->name,
                        $record->content,
                        $this->isActive($record->proxied),
                        $this->getDate($record->created_on),
                        $this->getDate($record->modified_on),
                    ];
                });

            if (count($data) > 0) {
                $this->table(['Type', 'Name', 'Content', 'Proxied','Created at','Modified at'], $data);
            } else {
                $this->fail('Sorry, we couldn\'t find anything to display');
            }
        } catch (EndpointException $exception) {
            $this->output->error('Could not find zones with specified name.');
        }
    }

    private function setOrder(): void
    {
        $this->order = strtolower($this->option('order')) ?? $this->order;

        if (! in_array($this->order, ['type', 'name', 'content', 'ttl', 'proxied'], true)) {
            $this->order = 'type';
        }
    }

    private function setMatch(): void
    {
        $this->match = strtolower($this->option('match')) ?? $this->match;

        if (! in_array($this->match, ['any', 'all'], true)) {
            $this->match = 'all';
        }
    }

    private function setDirection(): void
    {
        $this->direction = strtolower($this->option('direction')) ?? $this->direction;

        if (! in_array($this->direction, ['desc', 'asc'], true)) {
            $this->direction = 'desc';
        }
    }

    private function setPage(): void
    {
        $this->page = (int) ($this->option('page') ?? $this->page);
        $this->page = $this->page < 1 ? 1 : $this->page;
    }

    private function setLimit(): void
    {
        $this->limit = (int) ($this->option('limit') ?? $this->limit);
        $this->limit = $this->limit > 100 ? 100 : $this->limit;
        $this->limit = $this->limit < 5 ? 5 : $this->limit;
    }

    private function setType(): void
    {
        $this->type = strtoupper($this->option('type')) ?? '';

        $types = ['A', 'AAAA', 'CNAME', 'TXT', 'SRV', 'LOC', 'MX', 'NS', 'SPF', 'CERT', 'DNSKEY', 'DS', 'NAPTR', 'SMIMEA', 'SSHFP',
            'TLSA', 'URI', ];

        if (! in_array($this->type, $types, true)) {
            $this->type = '';
        }
    }
}
