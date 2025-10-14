<?php

namespace App\Services;

use App\Contracts\ApiDataHandlerInterface;
use App\Models\Account;
use App\Models\Income;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;

class SyncService
{
    private const PAGE_LIMIT = 500;
    readonly ApiClient $api;
    readonly string $endpoint;
    readonly string $modelClass;
    private ?string $fromDate;
    private ?string $toDate;
    private ApiDataHandlerInterface $service;
    private array $allData;

    public function __construct(
        ApiClient               $api,
//        ApiDataHandlerInterface $service,
        string                  $endpoint,
        string                  $modelClass,
        ?string                 $fromDate = null,
        ?string                 $toDate = null,
    ) {
        $this->api = $api;
        $this->endpoint = $endpoint;
        $this->modelClass = $modelClass;
        $this->fromDate = $fromDate ?? '2004-01-01';
        $this->toDate = $toDate ?? '2026-01-01';
        $this->allData = [];
//        $this->service = $service;
    }

    public function sync()
    {
        $page = 1;
        $totalSaved = 0;
        do {

            try {
                $response = $this->api->fetch($this->endpoint, [
                    'dateFrom'=> $this->fromDate,
                    'dateTo' => $this->toDate,
                    'page' => $page,
                    'limit' => self::PAGE_LIMIT,
                ]);
            } catch (RequestException $e) {
                if ($e->getCode() === 429) {
                    sleep(5);
                    continue;
                }
                throw $e;
            }
            $data = $response['data'] ?? [];
            $to = $response['meta']['to'] ?? 0;
            $total = $response['meta']['total'] ?? 0;
//            if (!empty($data)) {
//                $this->handleIncome($dataWithId);
//                $totalSaved += count($dataWithId);
//            }

            $usedMemory = round(memory_get_usage(true) / 1024 / 1024, 2);
            $percent = $total ? round($to / $total * 100) : 100;
            dump("Загружено {$percent}% ({$to}/{$total}), память={$usedMemory} MB");

            $page++;
            yield $data;
        } while ($to < $total);

        dump("{$this->endpoint} завершено. Всего сохранено: {$totalSaved}");
        return $totalSaved;
    }

    private function addAccountId(array $data): array
    {
        return array_map(function ($item) {
            $item['account_id'] = 1;
            return $item;
        }, $data);
    }

    public function syncStocks()
    {
        $params = [];
        DB::transaction(function () use ($params) {
            Stock::where('account_id', 1)->delete();
            $this->sync();
        });
    }

    public function handleSales(array $data)
    {
        dump(Sale::upsert($data, ['account_id', 'g_number', 'nm_id', 'sale_id', 'date']));
    }

    public function handleStocks(array $data)
    {
        dump(Stock::insert($data));
    }

    public function handleIncome(array $data)
    {
        dump(Income::upsert($data, ['account_id', 'income_id', 'nm_id']));
    }

    public function handleOrders(array $data)
    {
        if (!Order::latest('date')->first()) {
            dump('Нет заказов в бд ');
            dump(Order::insert($data));
        } else {
            $this->allData[] = $data;
        }
    }

    public function syncOrders()
    {
        // проверить для accoiunt_id
        // добавить во все методы проверку по account_id где надо
        $latest = Order::latest('date')->first();
        if (!$latest) {
            dump('Нет заказов в бд ');
            return $this->sync();
        }
        $now = Carbon::now()->toDateString();
        $curentDatetimeUpdate = Carbon::today()->setTime(12, 00)->toDateTimeString();
        if ($latest < $now) {
            $lastDayUpdate = $latest->date->toDateString();
            $ordersInDb = Order::where('order_date', '>=',$latest->date->setTime(00, 00))->get();
            $this->fromDate = $lastDayUpdate;
            $this->toDate = $curentDatetimeUpdate;
            $this->sync();
            $this->rejectSaved($this->allData, $ordersInDb);
            dump(Order::insert($data));

        }

    }

    public function rejectSaved($incoming, $fromDb)
    {
        $col = collect($fromDb);

        return collect($incoming)->reject(function($item) use ($col) {
            return $fromDb->containsStrict($item);
        });
    }
}
