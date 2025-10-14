<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Income;
use App\Models\Sale;
use App\Models\User;
use App\Services\SyncService;
use App\Services\WbReports\WbReportsService;
use Illuminate\Console\Command;

class SyncSales extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:sales';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync sales with API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $account = Account::find(1);
        (new WbReportsService($account))->syncSales();
        return self::SUCCESS;
    }
}
