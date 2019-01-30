<?php

namespace Tests\App\Commands;

use Cloudflare\API\Endpoints\User;
use Tests\TestCase;

class UserCommandsTest extends TestCase
{
    public function testGetUserDetailsCommand()
    {
        $this->mockUser()
            ->andReturn($this->getFixtures('getUserDetails')->result);

        $this->artisan('user:details')
            ->expectsOutput('Get current user details')
            ->expectsOutput('| ID                 | 7c5dae5552338874e5053f2534d2767a |')
            ->expectsOutput('| Email              | user@example.com                 |')
            ->assertExitCode(0);
    }

    public function testGetUserIdCommand(): void
    {
        $this->mockUser('getUserID')
            ->andReturn($this->getFixtures('getUserDetails')->result->id);

        $this->artisan('user:id')
            ->expectsOutput('7c5dae5552338874e5053f2534d2767a')
            ->assertExitCode(0);
    }

    public function testGetUserEmailCommand(): void
    {
        $this->mockUser('getUserEmail')
            ->andReturn($this->getFixtures('getUserDetails')->result->email);

        $this->artisan('user:email')
            ->expectsOutput('user@example.com')
            ->assertExitCode(0);
    }

    /**
     * This function will Mock the User API Endpoint.
     *
     * @param string $function
     * @return mixed
     */
    protected function mockUser($function = 'getUserDetails')
    {
        return $this->mock(User::class)
            ->shouldReceive($function)
            ->once();
    }
}

