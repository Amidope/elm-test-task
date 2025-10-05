<?php

namespace App\Providers;

use App\Console\Commands\SyncAll;
use App\Console\Commands\SyncIncomes;
use App\Console\Commands\SyncOrders;
use App\Console\Commands\SyncSales;
use App\Console\Commands\SyncStocks;
use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use App\Services\ApiClient;
use App\Services\SyncService;
use Illuminate\Support\ServiceProvider;

class SyncServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SyncOrders::class, fn($app) =>
            new SyncOrders(
                new SyncService(
                    $app->make(ApiClient::class),
                    endpoint: 'orders',
                    modelClass: Order::class,
                    fromDate: '2004-01-01',
                    toDate: now()->format('Y-m-d')
                )
            )
        );

        $this->app->singleton(SyncSales::class, fn($app) =>
            new SyncSales(
                new SyncService(
                    $app->make(ApiClient::class),
                    endpoint: 'sales',
                    modelClass: Sale::class,
                    fromDate: '2004-01-01',
                    toDate: now()->format('Y-m-d')
                )
            )
        );

        $this->app->singleton(SyncIncomes::class, fn($app) =>
            new SyncIncomes(
                new SyncService(
                    $app->make(ApiClient::class),
                    endpoint: 'incomes',
                    modelClass: Income::class,
                    fromDate: '2004-01-01',
                    toDate: now()->format('Y-m-d')
                )
            )
        );

        $this->app->singleton(SyncStocks::class, fn($app) =>
            new SyncStocks(
                new SyncService(
                    $app->make(ApiClient::class),
                    endpoint: 'stocks',
                    modelClass: Stock::class,
                    fromDate: now()->format('Y-m-d'),
                    toDate: null
                )
            )
        );
        $this->app->singleton(SyncAll::class, fn($app) =>
        new SyncAll([
            $app->make(SyncOrders::class),
            $app->make(SyncSales::class),
            $app->make(SyncIncomes::class),
            $app->make(SyncStocks::class),
        ])
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
