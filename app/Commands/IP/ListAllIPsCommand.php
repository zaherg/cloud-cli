<?php

namespace App\Commands\IP;

use Cloudflare\API\Endpoints\IPs;
use LaravelZero\Framework\Commands\Command;

class ListAllIPsCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'ips:list-all';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get Cloudflare IPs';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\IPs $ips
     *
     * @return mixed
     */
    public function handle(IPs $ips)
    {
        $this->output->title($this->description);
        $results = $ips->listIPs();

        $items = $this->formatArray($results);

        $header = ['IPv4', 'IPv6'];
        $this->table($header, $items);
    }

    private function formatArray($results)
    {
        $ipv4 = collect($results->ipv4_cidrs);
        $ipv6 = collect($results->ipv6_cidrs);

        if ($ipv4->count() > $ipv6->count()) {
            $data = collect($ipv4)->map(function ($item, $key) use ($ipv6) {
                return [$item, $ipv6[$key] ?? null];
            });
        } else {
            $data = collect($ipv6)->map(function ($item, $key) use ($ipv4) {
                return [$item, $ipv4[$key] ?? null];
            });
        }

        return $data->toArray();
    }
}
