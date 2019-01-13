<?php

namespace App\Commands\User;

use Cloudflare\API\Endpoints\User;
use LaravelZero\Framework\Commands\Command;

class UpdateCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'user:update
                            {--first_name= : Whether the job should be queued}
                            {--last_name= : Whether the job should be queued}
                            {--telephone= : Whether the job should be queued}
                            {--country= : Whether the job should be queued}
                            {--zipcode= : Whether the job should be queued}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Update the current user personal data';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\User $user
     *
     * @return mixed
     */
    public function handle(User $user)
    {
        $values = array_where($this->prepareOptions(), function ($value) {
            return null !== $value;
        });

        if (count($values) > 0) {
            $user->updateUserDetails($values);
        }

        $this->output->success('User information has been updated with the data you provided');

        $this->call('user:details');
    }

    protected function prepareOptions(): array
    {
        return [
            'first_name' => $this->option('first_name'),
            'last_name' => $this->option('last_name'),
            'telephone' => $this->option('telephone'),
            'country' => $this->option('country'),
            'zipcode' => $this->option('zipcode'),
        ];
    }
}
