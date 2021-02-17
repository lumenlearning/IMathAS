<?php

namespace App\Providers\Repositories;

use Illuminate\Support\ServiceProvider;

class AssessmentRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Interfaces\AssessmentRepositoryInterface',
            // To change the data source, replace this class name
            // with another implementation
            'App\Repositories\ohm\AssessmentRepository'
        );
    }
}