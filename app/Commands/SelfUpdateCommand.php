<?php

namespace App\Commands;

use App\Traits\CommonTrait;
use Humbug\SelfUpdate\Updater;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    use CommonTrait;

    protected $githubRepo = 'zaherg/cloud-cli';
    protected $pharFileName = 'cloud';
    protected $stability = 'stable';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'self-update
                            {--dev : Update to the dev (unstable) version}
                            {--any : Update to the dev (any) version}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Updates cloud cli phar file to the latest version';

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        if ($this->option('dev')) {
            $this->stability = 'unstable';
        }

        if ($this->option('any')) {
            $this->stability = 'any';
        }
    }

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
