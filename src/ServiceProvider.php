<?php

namespace Vochina\HeepayCustomer;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(HeepayCustomer::class, function () {
            return new HeepayCustomer(config('services.heepay-customer.sm2PrivateKeyPath'), config('services.heepay-customer.sm2publicKeyPath'));
        });

        $this->app->alias(HeepayCustomer::class, 'heepay-customer');
    }

    public function provides()
    {
        return [HeepayCustomer::class, 'heepay-customer'];
    }
}