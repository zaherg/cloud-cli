<?php

namespace App\Commands;

use App\Traits\CommonTrait;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class InitCommand extends Command
{
    use CommonTrait;
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Create the default config environment variables';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->output->title($this->description);

        $email = strtolower($this->ask('What is your CloudFlare email?'));
        $key = $this->ask('What is your CloudFlare API KEY');

        $this->task($this->description, function () use ($email, $key) {
            return Storage::disk('local')
                ->put('.env', "AUTH_EMAIL={$email} \nAUTH_KEY={$key}");
        });
    }
}
