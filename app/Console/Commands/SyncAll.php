<?php

namespace App\Console\Commands;

use App\Jobs\SyncIncomesJob;
use App\Jobs\SyncOrdersJob;
use App\Jobs\SyncSalesJob;
use App\Jobs\SyncStocksJob;
use App\Models\Account;
use Illuminate\Console\Command;

class SyncAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:all {account-id? : ID аккаунта}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Асинхронная синхронизация всех данных';

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

        $this->info('===== Начало полной синхронизации =====');
        $this->info("Добавление задач в очередь для {$accounts->count()} аккаунтов...");
        $this->info('');

        foreach ($accounts as $account) {
            $this->info("Аккаунт ID {$account->id} ({$account->name}):");

            SyncSalesJob::dispatch($account->id);
            $this->line("  ✓ Sales job добавлен");

            SyncOrdersJob::dispatch($account->id);
            $this->line("  ✓ Orders job добавлен");

            SyncStocksJob::dispatch($account->id);
            $this->line("  ✓ Stocks job добавлен");

            SyncIncomesJob::dispatch($account->id);
            $this->line("  ✓ Incomes job добавлен");

            $this->info('');
        }

        $totalJobs = $accounts->count() * 4;

        $this->info('===== Все задачи добавлены в очередь! =====');
        $this->info("Всего jobs в очереди: {$totalJobs}");
        $this->info('Смотрите прогресс: docker-compose logs -f queue');

        return 0;
    }
}
