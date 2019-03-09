<?php

namespace App\Kernel;

use LaravelZero\Framework\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /**
     * {@inheritdoc}
     */
    protected $bootstrappers = [
        \App\Bootstrap\Bootstrap::class,
        \LaravelZero\Framework\Bootstrap\CoreBindings::class,
        \LaravelZero\Framework\Bootstrap\LoadEnvironmentVariables::class,
        \LaravelZero\Framework\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \LaravelZero\Framework\Bootstrap\RegisterFacades::class,
        \LaravelZero\Framework\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ];
}
