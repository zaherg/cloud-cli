<?php

namespace App\Bootstrap;

use Dotenv\Dotenv;
use LaravelZero\Framework\Application;
use LaravelZero\Framework\Contracts\BoostrapperContract;

final class Bootstrap implements BoostrapperContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app): void
    {
        if (class_exists(Dotenv::class) && \Phar::running() !== '') {
            $app->make(LoadEnvironmentFile::class)->bootstrap($app);
        }
    }
}
