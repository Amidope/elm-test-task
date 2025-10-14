<?php

namespace App\Services\WbReports;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\ApiToken;
use App\Models\Sale;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Income;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class WbReportsService
{
    private WbReportsClient $client;
    readonly Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
        $serviceName = 'wb-reports-api';
        $token = Account::find(1)->getTokenForService($serviceName);
        $baseUrl = ApiService::where(['name' => $serviceName])->first()->base_url;
        $this->client = new WbReportsClient($baseUrl, $token);
    }

    /**
     * Синхронизировать продажи.
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return int
     * @throws Exception
     */
    public function syncSales(): int
    {
        $saved = 0;

        $params = [
            'dateFrom' => '2004-01-01',
            'dateTo' => '2029-01-01'
        ];
        $generator = $this->client->sync('sales', $params);
        foreach ($generator as $pageData) {
            $dataWithId = addAccountId($pageData, $this->account);
            $saved += count($dataWithId);
            dump(Sale::upsert($dataWithId, ['account_id', 'g_number', 'nm_id', 'sale_id', 'date']));
        }

        return $saved;
    }

    /**
     * Синхронизировать заказы.
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return int
     * @throws Exception
     */
    public function syncOrders(): int
    {
        $saved = 0;
        $params = [
            'dateFrom' => '2004-01-01',
            'dateTo' => Carbon::now()->toDateTimeString()
        ];
        $lastOrder = Order::latest('date')->first();

        if (!$lastOrder) {
            DB::transaction(function () use ($params, &$saved) {
                dump('Нет заказов в бд ');
                $generator = $this->client->sync('orders', $params);
                foreach ($generator as $pageData) {
                    $dataWithId = addAccountId($pageData, $this->account);
                    $saved += count($dataWithId);
                    dump(Order::insert($dataWithId));
                }
            });
            return $saved;
        }

        $lastDayUpdate = Carbon::parse($lastOrder->date)->toDateString();
        $params['dateFrom'] = $lastDayUpdate;

        $newOrders = [];
        $generator = $this->client->sync('orders', $params);

        foreach ($generator as $pageData) {
            $dataWithId = addAccountId($pageData, $this->account);
            $saved += count($dataWithId);
            $newOrders = array_merge($newOrders, $dataWithId);
        }

        $ordersFromLastDate = Order::where(
            'date',
            '>=',
            Carbon::parse($lastOrder->date)->setTime(00, 00)
        )->get()->toArray();

        $filtered = rejectSaved(normalizeOrdersForCompare($newOrders), $ordersFromLastDate);

        dump(count($newOrders));
        dump(count($ordersFromLastDate));
        dump(count($filtered));
        dump('записываю');


        collect($filtered)
            ->chunk(1000)
            ->each(fn($chunk) => Order::insert($chunk->toArray()));

        return $saved;
    }

    /**
     * Синхронизировать остатки.
     *
     * @return int
     * @throws Exception
     */
    public function syncStocks(): int
    {
        $params = [
            'dateFrom' => Carbon::today()->toDateString(),
            'dateTo' => null
        ];
        $saved = 0;
        DB::transaction(function () use ($params, &$saved) {
            Stock::where('account_id', $this->account->id)->delete();
            $generator = $this->client->sync('stocks', $params);

            foreach ($generator as $pageData) {
                $dataWithId = addAccountId($pageData, $this->account);
                $saved += count($dataWithId);
                dump(Stock::insert($dataWithId));
            }
        });

        return $saved;
    }

    /**
     * Синхронизировать поставки.
     *
     * @param Carbon $dateFrom
     * @param Carbon $dateTo
     * @return int
     * @throws Exception
     */
    public function syncIncomes(Carbon $dateFrom = null, Carbon $dateTo = null): int
    {
        $saved = 0;

        $params = [
            'dateFrom' => '2004-01-01',
            'dateTo' => '2029-01-01'
        ];
        $generator = $this->client->sync('incomes', $params);
        foreach ($generator as $pageData) {
            $dataWithId = addAccountId($pageData, $this->account);
            $saved += count($dataWithId);
            dump(Income::upsert($dataWithId, ['account_id', 'income_id', 'nm_id']));
        }

        return $saved;
    }



}
