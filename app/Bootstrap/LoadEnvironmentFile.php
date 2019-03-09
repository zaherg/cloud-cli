<?php

namespace App\Bootstrap;

use Dotenv\Dotenv;
use LaravelZero\Framework\Application;
use Dotenv\Exception\InvalidFileException;
use Dotenv\Exception\InvalidPathException;
use LaravelZero\Framework\Contracts\BoostrapperContract;

final class LoadEnvironmentFile implements BoostrapperContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(Application $app): void
    {
        try {
            (new Dotenv(getEnvPath(), '.env'))->overload();
        } catch (InvalidPathException $e) {
            echo 'The path is invalid: ' . $e->getMessage();
            die(1);
        } catch (InvalidFileException $e) {
            echo 'The environment file is invalid: ' . $e->getMessage();
            die(1);
        }
    }
}
