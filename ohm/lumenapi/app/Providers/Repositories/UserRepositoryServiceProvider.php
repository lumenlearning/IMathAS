<?php

namespace App\Providers\Repositories;

use Illuminate\Support\ServiceProvider;

class UserRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Interfaces\UserRepositoryInterface',
            // To change the data source, replace this class name
            // with another implementation
            'App\Repositories\ohm\UserRepository'
        );
    }
}