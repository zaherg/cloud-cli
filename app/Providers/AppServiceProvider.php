<?php

namespace App\Providers;

use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\IPs;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\User;
use Cloudflare\API\Endpoints\Zones;
use Illuminate\Support\ServiceProvider;
use App\Exceptions\NoConfigFileException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        $this->registerAPIKey();

        $this->registerUser();

        $this->registerZones();

        $this->registerDNS();

        $this->registerIPs();
    }

    protected function registerAPIKey(): void
    {
        $this->app->singleton(APIKey::class, function ($app) {
            if ('fake@mail.com' === config('cloudflare.email') || 'dummyAuthKey' === config('cloudflare.key')) {
                throw new NoConfigFileException('Please make sure to run the init command first');
            }

            return new APIKey(config('cloudflare.email'), config('cloudflare.key'));
        });
    }

    protected function registerUser(): void
    {
        $this->app->singleton(User::class, function ($app) {
            $adapter = new Guzzle($app->make(APIKey::class));

            return new User($adapter);
        });
    }

    public function registerZones(): void
    {
        $this->app->singleton(Zones::class, function ($app) {
            $adapter = new Guzzle($app->make(APIKey::class));

            return new Zones($adapter);
        });
    }

    public function registerDNS(): void
    {
        $this->app->singleton(DNS::class, function ($app) {
            $adapter = new Guzzle($app->make(APIKey::class));

            return new DNS($adapter);
        });
    }

    protected function registerIPs(): void
    {
        $this->app->singleton(IPs::class, function ($app) {
            $adapter = new Guzzle($app->make(APIKey::class));

            return new IPs($adapter);
        });
    }
}
