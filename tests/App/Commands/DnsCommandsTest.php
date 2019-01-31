<?php

namespace Tests\App\Commands;

use Tests\TestCase;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;

class DnsCommandsTest extends TestCase
{
    /*
     * dns:add               Create a new DNS record for a zone
     */
    public function testDnsAddCommandWhileAskingQuestions(): void
    {
        $this->mockZone();
        $this->mockDnsForManipulation();

        $this->artisan('dns:add')
            ->expectsQuestion('What is the domain name for the zone', 'example.com')
            ->expectsQuestion('DNS record name. Valid values can be : example.com, mail.example.com', 'example.com')
            ->expectsQuestion('The DNS record type. Valid values are: A, AAAA, CNAME, TXT, SRV, LOC, MX, NS, SPF, CERT, DNSKEY, DS, NAPTR, SMIMEA, SSHFP, TLSA, URI', 'A')
            ->expectsQuestion('DNS record content', '1.2.3.4')
            ->expectsOutput('A new record example.com has been added to your DNS Zone : example.com .')
            ->assertExitCode(0);

        $this->assertCommandCalled('dns:add');
    }

    /*
     * dns:add               Create a new DNS record for a zone
     */
    public function testDnsAddCommandByPassingValues(): void
    {
        $this->mockZone();
        $this->mockDnsForManipulation();

        $this->artisan('dns:add', [
            'domain' => 'example.com',
            '--type' => 'a',
            '--name' => 'example.com',
            '--content' => '1.2.3.4',
        ])
            ->expectsOutput('A new record example.com has been added to your DNS Zone : example.com .')
            ->assertExitCode(0);

        $this->assertCommandCalled('dns:add', [
            'domain' => 'example.com',
            '--type' => 'a',
            '--name' => 'example.com',
            '--content' => '1.2.3.4',
        ]);
    }

    /*
     * dns:list-records      List, search, sort, and filter a zones' DNS records
     */
    public function testDnsListRecordsCommand(): void
    {
        $this->mockDNS();

        $this->mockZone();

        $this->artisan('dns:list-records', ['domain' => 'example.com'])
            ->expectsOutput('List, search, sort, and filter a zones\' DNS records')
            ->expectsOutput('| A    | example.com | 1.2.3.4 | No      | Wed, Jan 1, 2014 5:20 AM | Wed, Jan 1, 2014 5:20 AM |')
            ->assertExitCode(0);

        $this->assertCommandCalled('dns:list-records', ['domain' => 'example.com']);
    }

    /*
     * dns:details           DNS Record Details
     */
    public function testGetDnsDetailsCommand(): void
    {
        $this->mockDNS();

        $this->mockZone();

        $this->artisan('dns:details', ['domain' => 'example.com', '--name' => 'example.com'])
            ->expectsOutput('DNS Record Details')
            ->expectsOutput('Record ID: 372e67954025e0ba6aaa6d586b9e0b59')
            ->expectsOutput('Record type: A')
            ->expectsOutput('Record name: example.com')
            ->expectsOutput('Record zone name: example.com')
            ->assertExitCode(0);

        $this->assertCommandCalled('dns:details', ['domain' => 'example.com', '--name' => 'example.com']);
    }

//    /*
//     * dns:update            Update DNS Record
//     */
//    public function testDnsEditCommand(): void
//    {
//        $this->markTestIncomplete('This test has not been implemented yet.');
//    }
//
//    /*
//     * dns:delete            Delete DNS Record
//     */
//    public function testDnsDeleteCommand(): void
//    {
//        $this->markTestIncomplete('This test has not been implemented yet.');
//    }

    protected function mockZone()
    {
        return $this->mock(Zones::class)
            ->shouldReceive('getZoneID')
            ->once()
            ->andReturn('023e105f4ecef8ad9ca31a8372d0c353');
    }

    protected function mockDNS($function = 'listRecords', $file = 'listRecords')
    {
        $this->mock(DNS::class)
            ->shouldReceive($function)
            ->once()
            ->andReturn($this->getFixtures($file));
    }

    protected function mockDnsForManipulation($function = 'addRecord'): void
    {
        $dns = $this->createMock(DNS::class);
        $dns->method($function)
            ->with(
                $this->equalTo('023e105f4ecef8ad9ca31a8372d0c353'),
                $this->equalTo('A'),
                $this->equalTo('example.com'),
                $this->equalTo('1.2.3.4'),
                $this->equalTo(120),
                $this->equalTo(false),
                $this->equalTo(10)
            )
            ->willReturn(true);

        $this->instance(DNS::class, $dns);
    }
}
