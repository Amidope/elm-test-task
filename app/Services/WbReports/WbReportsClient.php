<?php

namespace App\Services\WbReports;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use Generator;

class WbReportsClient
{
    private string $baseUrl;
    private string $apiKey;
    private int $maxRetries = 10;
    private int $retryDelay = 5; // секунды

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    /**
     * GET запрос с retry для 429.
     *
     * @param string $endpoint
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function get(string $endpoint, array $params = []): array
    {
        $params['key'] = $this->apiKey;
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::timeout(30)
                    ->get("{$this->baseUrl}/api/{$endpoint}", $params);
                return $response->json() ?? [];

            } catch (RequestException $e) {
                if ($e->getCode() === 429) {
                    $attempt++;
                    if ($attempt >= $this->maxRetries) {
                        Log::error('WB Reports API Rate Limit Exceeded', [
                            'endpoint' => $endpoint,
                            'attempts' => $attempt,
                        ]);

                        throw new \Exception(
                            "Rate limit exceeded after {$this->maxRetries} attempts"
                        );
                    }
                    sleep($this->retryDelay);
                    continue;
                }

                Log::error('WB Reports Request Exception', [
                    'endpoint' => $endpoint,
                    'message' => $e->getMessage(),
                ]);

                throw new \Exception("Failed to connect to WB Reports API: {$e->getMessage()}");
            }
        }

    }

    /**
     * Получить данные порциями через Generator.
     *
     * @param string $endpoint
     * @param array $params
     * @param int $limit
     * @return Generator
     * @throws \Exception
     */
    public function fetchPaginated(string $endpoint, array $params = [], int $limit = 500): Generator
    {
        $page = 1;
        $totalSaved = 0;

        do {
            $params['page'] = $page;
            $params['limit'] = $limit;

            $response = $this->get($endpoint, $params);

            $data = $response['data'] ?? [];
            $to = $response['meta']['to'] ?? 0;
            $total = $response['meta']['total'] ?? 0;

            if (!empty($data)) {
                yield $data;
                $totalSaved += count($data);
            }

            $page++;

        } while ($to < $total);

        Log::info("Fetched {$endpoint} data", [
            'pages' => $page - 1,
            'total_records' => $totalSaved,
            'expected_total' => $total,
        ]);
    }
}
