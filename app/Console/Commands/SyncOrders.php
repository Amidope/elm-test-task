<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrdersJob;
use App\Models\Account;
use App\Services\SyncService;
use App\Services\WbReports\WbReportsService;
use Illuminate\Console\Command;

class SyncOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:orders {account-id? : ID аккаунта}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Асинхронная синхронизация заказов';

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
        $accountId = $this->argument('account-id');

        $accounts = $accountId
            ? Account::where('id', $accountId)->where('is_active', true)->get()
            : Account::where('is_active', true)->get();

        if ($accounts->isEmpty()) {
            $this->error('Активные аккаунты не найдены');
            return 1;
        }

        $this->info("Добавление задач в очередь для {$accounts->count()} аккаунтов...");

        foreach ($accounts as $account) {
            SyncOrdersJob::dispatch($account->id);
            $this->info("✓ Job добавлен для аккаунта ID {$account->id}");
        }

        $this->info('Все задачи добавлены в очередь!');
        return 0;
    }
}
