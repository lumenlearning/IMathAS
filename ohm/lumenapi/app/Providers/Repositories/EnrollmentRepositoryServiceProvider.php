<?php

namespace App\Providers\Repositories;

use Illuminate\Support\ServiceProvider;

class EnrollmentRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Interfaces\EnrollmentRepositoryInterface',
            // To change the data source, replace this class name
            // with another implementation
            'App\Repositories\ohm\EnrollmentRepository'
        );
    }
}
