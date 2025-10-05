<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ApiClient
{
    protected string $baseUrl;
    protected string $key;
    protected int $timeout = 30;

    public function __construct()
    {
        $ip = config('services.reportApi.ip');
        $port = config('services.reportApi.port');
        $this->baseUrl = "http://{$ip}:{$port}/api/";
        $this->key = config('services.reportApi.key');
    }

    public function fetch(string $endpoint, array $params = []): array
    {
        $params['key'] = $this->key;
        $url = "{$this->baseUrl}{$endpoint}";

        $response = Http::timeout($this->timeout)
            ->acceptJson()
            ->get($url, $params)
            ->throw();

        return $response->json() ?? [];

    }
}
