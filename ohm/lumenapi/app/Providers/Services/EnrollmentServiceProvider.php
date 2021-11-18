<?php

namespace App\Providers\Services;

use Illuminate\Support\ServiceProvider;

class EnrollmentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Services\Interfaces\EnrollmentServiceInterface',
            'App\Services\ohm\EnrollmentService'
        );
    }
}
