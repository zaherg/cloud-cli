<?php

namespace App\Commands\User;

use Cloudflare\API\Endpoints\User;
use LaravelZero\Framework\Commands\Command;

class GetUserIDCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'user:id';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get the current user id';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\User $user
     *
     * @return mixed
     */
    public function handle(User $user)
    {
        $this->output->title($this->description);

        $this->line($user->getUserID());
    }
}
