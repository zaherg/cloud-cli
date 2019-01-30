<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
 */

if (! function_exists('includeIfExists')) {
    function includeIfExists($file)
    {
        return file_exists($file) ? include $file : false;
    }
}

if ((! $autoloader = includeIfExists(__DIR__ . '/../vendor/autoload.php')) && (! $autoloader = includeIfExists(__DIR__ . '/../../../autoload.php'))) {
    echo 'You must set up the project dependencies using `composer install`' . PHP_EOL .
        'See https://getcomposer.org/download/ for instructions on installing Composer' . PHP_EOL;
    exit(1);
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
 */

$app = new LaravelZero\Framework\Application(
    dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
 */

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    LaravelZero\Framework\Kernel::class
);

//$app->singleton(
//    Illuminate\Contracts\Debug\ExceptionHandler::class,
//    Illuminate\Foundation\Exceptions\Handler::class
//);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Set the correct path for the environment file
|--------------------------------------------------------------------------
|
| If the current directory has a .env file then we will use that instead
| of the global one, other wise the one under the user homepage.
|
 */

$app->instance('path.env', getEnvPath());

if (! file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env')) {
    $app->useEnvironmentPath(getEnvPath());
}

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
 */

return $app;
