<?php

namespace App\Commands\IP;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\IPs;
use GuzzleHttp\Exception\ClientException;
use LaravelZero\Framework\Commands\Command;

class ListAllIPsCommand extends Command
{
    use CommonTrait;
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
        try {
            $this->output->title($this->description);
            $results = $ips->listIPs();

            ['header' =>$header, 'items' => $items] = $this->formatArray($results);

            $this->line('| '.$items[0][0].'  | '.$items[0][1].' |');

            $this->table($header, $items);
        } catch (ClientException $exception) {
            $this->fail('Sorry something went wrong.');
        }
    }

    private function formatArray($results)
    {
        $ipv4 = collect($results->ipv4_cidrs);
        $ipv6 = collect($results->ipv6_cidrs);

        $data['header'] = ['IPv4', 'IPv6'];

        if ($ipv4->count() > $ipv6->count()) {
            $data['items'] = collect($ipv4)->map(function ($item, $key) use ($ipv6) {
                return [$item, $ipv6[$key] ?? null];
            })->toArray();
        } elseif($ipv4->count() === $ipv6->count()) {
            $data['items'] = collect($ipv4)->map(function ($item, $key) use ($ipv6) {
                return [$item, $ipv6[$key] ?? null];
            })->toArray();
        } else {
            $data['items'] = collect($ipv6)->map(function ($item, $key) use ($ipv4) {
                return [$item, $ipv4[$key] ?? null];
            })->toArray();

            $data['header'] = ['IPv6', 'IPv4'];
        }

        return $data;
    }
}
