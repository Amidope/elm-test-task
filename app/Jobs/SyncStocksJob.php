<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\WbReports\WbReportsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncStocksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;
    public $tries = 2;

    protected int $accountId;

    public function __construct(int $accountId)
    {
        $this->accountId = $accountId;
    }

    public function handle()
    {
        $account = Account::find($this->accountId);

        if (!$account || !$account->is_active) {
            Log::warning("Аккаунт ID {$this->accountId} не найден или неактивен");
            return;
        }

        dump("Job: Начало синхронизации остатков для аккаунта ID {$this->accountId}");

        try {
            $service = new WbReportsService($account);
            $saved = $service->syncStocks();

            dump("Job: Обработано {$saved} записей для аккаунта ID {$this->accountId}");
            Log::info("Синхронизация остатков для аккаунта ID {$this->accountId} завершена. Записей: {$saved}");

        } catch (\Exception $e) {
            Log::error("Ошибка синхронизации остатков для аккаунта ID {$this->accountId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Job провалился для аккаунта ID {$this->accountId}: " . $exception->getMessage());
    }
}
