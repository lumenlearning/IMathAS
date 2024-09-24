<?php

namespace App\Providers\Services;

use Illuminate\Support\ServiceProvider;

class QuestionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Services\Interfaces\QuestionServiceInterface',
            'App\Services\ohm\QuestionService'
        );
    }
}
