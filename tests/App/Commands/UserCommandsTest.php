<?php

namespace Tests\App\Commands;

use Tests\TestCase;
use Cloudflare\API\Endpoints\User;

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

        $this->assertCommandCalled('user:details');
    }

    public function testGetUserIdCommand(): void
    {
        $this->mockUser('getUserID')
            ->andReturn($this->getFixtures('getUserDetails')->result->id);

        $this->artisan('user:id')
            ->expectsOutput('7c5dae5552338874e5053f2534d2767a')
            ->assertExitCode(0);

        $this->assertCommandCalled('user:id');
    }

    public function testGetUserEmailCommand(): void
    {
        $this->mockUser('getUserEmail')
            ->andReturn($this->getFixtures('getUserDetails')->result->email);

        $this->artisan('user:email')
            ->expectsOutput('user@example.com')
            ->assertExitCode(0);

        $this->assertCommandCalled('user:email');
    }

    public function testUpdateUserInformationCommand(): void
    {
        $user = $this->createMock(User::class);
        $user->method('updateUserDetails')
            ->with($this->equalTo(['first_name' => 'John', 'last_name' => 'Doe']))
            ->willReturn($this->getFixtures('updateUserDetails'));

        $user->method('getUserDetails')
            ->willReturn($this->getFixtures('updateUserDetails')->result);

        $this->instance(User::class, $user);

        $this->artisan('user:update', ['--first_name' => 'John', '--last_name' => 'Doe'])
            ->expectsOutput('Get current user details')
            ->expectsOutput('| ID                 | 7c5dae5552338874e5053f2534d2767a |')
            ->expectsOutput('| Email              | user2@example.com                |')
            ->expectsOutput('| Name               | Doe, John                        |')
            ->assertExitCode(0);

        $this->assertCommandCalled('user:update', ['--first_name' => 'John', '--last_name' => 'Doe']);

        $this->assertCommandCalled('user:details');
    }

    /**
     * This function will Mock the User API Endpoint.
     *
     * @param string $function
     *
     * @return mixed
     */
    protected function mockUser($function = 'getUserDetails')
    {
        return $this->mock(User::class)
            ->shouldReceive($function)
            ->once();
    }
}
