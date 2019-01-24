<?php

namespace App\Commands\User;

use App\Traits\CommonTrait;
use Cloudflare\API\Endpoints\User;
use LaravelZero\Framework\Commands\Command;

class DetailsCommand extends Command
{
    use CommonTrait;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'user:details';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get current user details.';

    /**
     * Execute the console command.
     *
     * @param \Cloudflare\API\Endpoints\User $user
     *
     * @return mixed
     */
    public function handle(User $user)
    {
        $currentUserDetails = $user->getUserDetails();

        $this->output->title($this->description);

        $this->table(['Key', 'Value'], $this->generateTable($currentUserDetails));
    }

    protected function generateTable($currentUserDetails): array
    {
        return  [
            ['ID', $currentUserDetails->id],
            ['Email', $currentUserDetails->email],
            ['Name', sprintf('%s, %s', $currentUserDetails->last_name, $currentUserDetails->first_name)],
            ['Telephone', $currentUserDetails->telephone],
            ['Country', $currentUserDetails->country],
            ['Zipcode', $currentUserDetails->zipcode],
            ['2FA enabled', $this->isActive($currentUserDetails->two_factor_authentication_enabled)],
            ['2FA locked', $this->isActive($currentUserDetails->two_factor_authentication_locked)],
            ['Pro account', $this->isActive($currentUserDetails->has_pro_zones)],
            ['Business account', $this->isActive($currentUserDetails->has_business_zones)],
            ['Enterprise account', $this->isActive($currentUserDetails->has_enterprise_zones)],
            ['Suspended', $this->isActive($currentUserDetails->suspended)],
            ['Created On', $this->getDate($currentUserDetails->created_on)],
            ['Modified On', $this->getDate($currentUserDetails->modified_on)],
        ];
    }
}
