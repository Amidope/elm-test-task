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
use App\Services\IncomeService;
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
