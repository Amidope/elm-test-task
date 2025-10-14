<?php

namespace App\Console\Commands;

use App\Jobs\SyncSalesJob;
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
    protected $signature = 'sync:sales {account-id? : ID аккаунта}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Асинхронная синхронизация продаж';

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
        // если передали id то синхронизация будет только для этого аккаунта
        $accounts = $accountId
            ? Account::where('id', $accountId)->where('is_active', true)->get()
            : Account::where('is_active', true)->get();

        if ($accounts->isEmpty()) {
            $this->error('Активные аккаунты не найдены');
            return 1;
        }

        $this->info("Добавление задач в очередь для {$accounts->count()} аккаунтов...");

        foreach ($accounts as $account) {
            SyncSalesJob::dispatch($account->id);
            $this->info("✓ Job добавлен для аккаунта ID {$account->id} ({$account->name})");
        }

        $this->info('');
        $this->info('Все задачи добавлены в очередь!');
        $this->info('Запустите worker: docker-compose exec app php artisan queue:work');

        return 0;
    }
}
