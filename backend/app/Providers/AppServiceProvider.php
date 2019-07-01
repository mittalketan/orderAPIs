<?php

namespace App\Providers;

use App\Libraries\Distance\DistanceMatrix;
use App\Libraries\Distance\DistanceMatrixInterface;
use App\Repositories\Order\EloquentOrder;
use App\Repositories\Order\OrderRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Repository Mapping
        $this->app->bind(OrderRepository::class, EloquentOrder::class);

        // Libraries Mapping
        $this->app->bind(DistanceMatrixInterface::class, DistanceMatrix::class);

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
