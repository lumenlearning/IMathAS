<?php

namespace App\Providers\Services;

use Illuminate\Support\ServiceProvider;

class QuestionImportServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Services\Interfaces\QuestionImportServiceInterface',
            'App\Services\ohm\QuestionImportService'
        );
    }
}
