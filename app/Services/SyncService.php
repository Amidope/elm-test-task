<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\RequestException;

class SyncService
{
    private const PAGE_LIMIT = 100;
    readonly ApiClient $api;
    readonly string $endpoint;
    readonly string $modelClass;
    readonly ?string $fromDate;
    readonly ?string $toDate;

    public function __construct(
        ApiClient $api,
        string $endpoint,
        string $modelClass,
        ?string $fromDate = null,
        ?string $toDate = null,
    ) {
        $this->api = $api;
        $this->endpoint = $endpoint;
        $this->modelClass = $modelClass;
        $this->fromDate = $fromDate ?? '2004-01-01';
        $this->toDate = $toDate ?? '2026-01-01';
    }

    public function sync(): int
    {
        dump($this->fromDate, $this->toDate);
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
            if (!empty($data)) {
                $b = collect($data)->get(59);
                $a = collect(collect($data)->get(58))->filter(function ($v, $k) use ($b) {
                    return !$v === $b[$k];
                });
                dd($a);
                dd(collect($data)->get(58));
//                $grouped = collect($data)->groupBy(['nm_id', 'warehouse_name', 'sc_code']);

//                $duplicateGroups = $grouped->filter(fn($group) => $group->count() > 1);
//                dd($duplicateGroups);

                $this->modelClass::upsert($data, ['nm_id', 'warehouse_name', 'sc_code']);
                $totalSaved += count($data);
            }

            $usedMemory = round(memory_get_usage(true) / 1024 / 1024, 2);
            $percent = $total ? round($to / $total * 100) : 100;
            dump("Загружено {$percent}% ({$to}/{$total}), память={$usedMemory} MB");

            $page++;
        } while ($to < $total);

        dump("{$this->endpoint} завершено. Всего сохранено: {$totalSaved}");
        return $totalSaved;
    }
}
