<?php

namespace Tests\App\Commands;

use Tests\TestCase;
use Cloudflare\API\Endpoints\IPs;

class IPsCommandsTest extends TestCase
{
    public function testListAllIpsCommand(): void
    {
        $this->mock(IPs::class)
            ->shouldReceive('listIPs')
            ->once()
            ->andReturn($this->getFixtures('listIPs')->result);

        $this->artisan('ips:list-all')
            ->expectsOutput('Get Cloudflare IPs')
            ->expectsOutput('| 199.27.128.0/21  | 2400:cb00::/32 |')
            ->assertExitCode(0);

        $this->assertCommandCalled('ips:list-all');
    }
}
