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

    protected $recordName;
    protected $recordType;
    protected $recordContent;
    protected $page = 1;
    protected $limit = 20;
    protected $order = 'type';
    protected $match = 'all';
    protected $direction = 'desc';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'dns:list-records
                            {--type= : Record type, valid values: A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF, CERT, DNSKEY, DS, NAPTR, SMIMEA, SSHFP, TLSA, URI}
                            {--name= : Record name, max length: 255}
                            {--content= : Record content}
                            {--limit=10 : Number of DNS records per page, default: 20, min :5, max :100}
                            {--page=1 : Page number of paginated results, default: 1, min :1}
                            {--order=type : Field to order records by, valid values: type, name, content, ttl, proxied}
                            {--direction=desc : Direction to order domains, valid values: desc or asc}
                            {--match=all : Whether to match all search requirements or at least one (any), valid values: any, all}
                            {domain : The domain name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'List, search, sort, and filter a zones\' DNS records.';

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->recordContent = $this->option('content') ?? '';

        $this->setRecordName();
        $this->setRecordType();
        $this->setPage();
        $this->setLimit();
        $this->setOrder();
        $this->setMatch();
        $this->setDirection();
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
                $this->recordType,
                $this->recordName,
                $this->recordContent,
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
                $this->table(['Type', 'Name', 'Content', 'Proxied', 'Created at', 'Modified at'], $data);
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

    private function setRecordType(): void
    {
        $this->recordType = strtoupper($this->option('type')) ?? '';

        $types = ['A', 'AAAA', 'CNAME', 'TXT', 'SRV', 'LOC', 'MX', 'NS', 'SPF', 'CERT', 'DNSKEY', 'DS', 'NAPTR', 'SMIMEA', 'SSHFP',
            'TLSA', 'URI', ];

        if (! in_array($this->recordType, $types, true)) {
            $this->recordType = '';
        }
    }

    private function setRecordName(): void
    {
        $this->recordName = strtolower($this->option('name')) ?? '';

        if (strlen($this->recordName) > 255) {
            $this->output->error('The DNS record name should not be more than 255 character.');
            exit;
        }
    }
}
