<?php

namespace App\Providers\Repositories;

use Illuminate\Support\ServiceProvider;

class LibraryItemRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            'App\Repositories\Interfaces\LibraryItemRepositoryInterface',
            // To change the data source, replace this class name
            // with another implementation
            'App\Repositories\ohm\LibraryItemRepository'
        );
    }
}