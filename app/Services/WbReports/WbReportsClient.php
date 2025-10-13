<?php

namespace App\Services\WbReports;
use App\Contracts\ApiDataHandlerInterface;
use App\Services\ApiClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class WbReportsClient
{
    private const PAGE_LIMIT = 500;

    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . "/api/";
        $this->apiKey = $apiKey;
    }

    public function get(string $endpoint, array $params = []): array
    {
        $params['key'] = $this->apiKey;
        $url = "{$this->baseUrl}{$endpoint}";

        $response = Http::timeout(30)
            ->acceptJson()
            ->get($url, $params)
            ->throw();

        return $response->json() ?? [];
    }

    public function sync(string $endpoint, array $params)
    {
        $params['limit'] = self::PAGE_LIMIT;
        $page = 1;
        $totalSaved = 0;
        do {

            try {
                $params['page'] = $page;
                $response = $this->get($endpoint, $params);
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

            $usedMemory = round(memory_get_usage(true) / 1024 / 1024, 2);
            $percent = $total ? round($to / $total * 100) : 100;
            dump("Загружено {$percent}% ({$to}/{$total}), память={$usedMemory} MB");

            $page++;
            yield $data;
        } while ($to < $total);
    }
}
