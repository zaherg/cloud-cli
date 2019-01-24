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
                            {--first_name= : First Name}
                            {--last_name= : Last Name}
                            {--telephone= : Telephone Number}
                            {--country= : Country Code}
                            {--zipcode= : Zip Code}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Edit part of your user details';

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
