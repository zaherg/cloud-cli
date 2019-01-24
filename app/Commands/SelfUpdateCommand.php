<?php

namespace App\Commands;

use App\Traits\CommonTrait;
use Humbug\SelfUpdate\Updater;
use LaravelZero\Framework\Commands\Command;

class SelfUpdateCommand extends Command
{
    use CommonTrait;

    protected $githubRepo = 'zaherg/cloudflare-cli';
    protected $pharFileName = 'cloudflare';
    protected $stability = 'stable';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'self-update
                            {--stability=stable : Set the stability flag for the downloaded file. Valid values: stable,unstable, any.}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Updates cloudflare phar file to the latest version';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->title($this->description);

        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName($this->githubRepo);
        $updater->getStrategy()->setPharName($this->pharFileName);
        $updater->getStrategy()->setCurrentLocalVersion(app('git.version'));

        if (null !== $this->option('stability') && in_array(strtolower($this->option('stability')), ['unstable', 'any'], true)) {
            $this->stability = strtolower($this->option('stability'));
        }

        $updater->getStrategy()->setStability($this->stability);

        try {
            $hasUpdate = $updater->hasUpdate();

            if ($hasUpdate) {
                $newVersion = $updater->getNewVersion();

                $this->info(sprintf('The current stable build available remotely is: %s', $newVersion));

                $result = $updater->update();

                $result ? $this->info('Updated!') : $this->fail('No update needed!');
            } elseif (false === $updater->getNewVersion()) {
                $this->warn('There are no stable builds available.');
            } else {
                $this->info('You have the current stable build installed.');
            }
        } catch (\Exception $e) {
            $this->fail('Well, something happened! Either an oopsie or something involving hackers.');
            exit(1);
        }
    }
}
