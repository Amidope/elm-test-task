<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Income;
use App\Services\SyncService;
use App\Services\WbReports\WbReportsService;
use Illuminate\Console\Command;

class SyncIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:incomes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync incomes with API';

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
        $apiToken = $account->apiTokens()->first();
        (new WbReportsService($apiToken))->syncIncomes($account);
        return self::SUCCESS;
    }
}
