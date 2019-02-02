<?php

namespace Tests\App\Commands;

use Tests\TestCase;
use Cloudflare\API\Endpoints\Zones;

class ZoneCommandsTest extends TestCase
{
    public function testListZonesCommand(): void
    {
        $this->mock(Zones::class)
            ->shouldReceive('listZones')
            ->andReturn($this->getFixtures('listZones'));

        $this->artisan('zone:list')
            ->expectsOutput('List, search, sort, and filter your zones')
            ->expectsOutput('| example.com | active | Pro Plan | Yes      | Wed, Jan 1, 2014 5:20 AM | Wed, Jan 1, 2014 5:20 AM |')
            ->assertExitCode(0);

        $this->assertCommandCalled('zone:list');
    }

    public function testZoneActivationCheckCommand(): void
    {
        $zone = $this->createMock(Zones::class);

        $zone->method('getZoneID')
            ->willReturn('023e105f4ecef8ad9ca31a8372d0c353');

        $zone->method('activationCheck')
            ->willReturn(true);

        $this->instance(Zones::class, $zone);

        $this->artisan('zone:check-activation', ['domain' => 'example.com'])
            ->expectsOutput('We have successfully initiated another zone activation check')
            ->assertExitCode(0);

        $this->assertCommandCalled('zone:check-activation', ['domain' => 'example.com']);
    }
}
