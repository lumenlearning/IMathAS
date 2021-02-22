<?php

namespace App\Providers\Repositories;

use Illuminate\Support\ServiceProvider;

class QuestionSetRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Interfaces\QuestionSetRepositoryInterface',
            // To change the data source, replace this class name
            // with another implementation
            'App\Repositories\ohm\QuestionSetRepository'
        );
    }
}