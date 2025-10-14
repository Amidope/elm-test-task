<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Services\SyncService;
use App\Services\WbReports\WbReportsService;
use Illuminate\Console\Command;

class SyncStocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:stocks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync stocks with API';

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
    public function handle(): int
    {
        $account = Account::find(1);
        $apiToken = $account->apiTokens()->first();
        $saved = (new WbReportsService($apiToken))->syncStocks($account);
        dump($saved);
        return self::SUCCESS;
    }
}
