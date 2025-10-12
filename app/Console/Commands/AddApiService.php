<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddApiService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-service:add
                          {name? : Название API сервиса}
                          {base_url? : Базовый URL API}
                          {--list : Показать список API сервисов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить новый API сервис';

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
        if ($this->option('list')) {
            $this->showApiServices();
            return 0;
        }

        $name = $this->argument('name');

        if (!$name) {
            $this->error('Укажите название API сервиса');
            return 1;
        }

        $baseUrl = $this->argument('base_url');

        if (!$baseUrl) {
            $this->error('Укажите базовый URL API');
            return 1;
        }

        $name = strtolower(trim($name));
        $baseUrl = trim($baseUrl);

        // Валидация URL
        if (!$this->isValidUrl($baseUrl)) {
            $this->error('Некорректный формат URL!');
            $this->line('URL должен начинаться с http:// или https://');
            $this->newLine();
            $this->line('Примеры корректных URL:');
            $this->line('  https://api.wildberries.ru');
            $this->line('  http://192.168.1.10:8080');
            $this->line('  https://ozon.ru/api/v1');
            return 1;
        }

        if (ApiService::where('name', $name)->exists()) {
            $this->error("API сервис '{$name}' уже существует!");
            $this->newLine();
            $this->showApiServices();
            return 1;
        }

        $tokenTypes = TokenType::orderBy('id')->get();

        if ($tokenTypes->isEmpty()) {
            $this->error('Нет доступных типов токенов! Создайте хотя бы один: php artisan token-type:add');
            return 1;
        }

        $this->info('Доступные типы токенов:');
        foreach ($tokenTypes as $type) {
            $this->line("  [{$type->id}] {$type->name}");
        }
        $this->newLine();

        $selectedIds = $this->ask('Укажите ID типов токенов через запятую (например: 1,2,3)');

        if (!$selectedIds) {
            $this->error('Необходимо выбрать хотя бы один тип токена!');
            return 1;
        }

        $ids = array_map('trim', explode(',', $selectedIds));
        $ids = array_filter($ids, 'is_numeric');

        if (empty($ids)) {
            $this->error('Некорректный формат ID!');
            return 1;
        }

        $validTypes = TokenType::whereIn('id', $ids)->get();

        if ($validTypes->count() !== count($ids)) {
            $this->error('Некоторые указанные ID не существуют!');
            return 1;
        }

        try {
            $apiService = ApiService::create([
                'name' => $name,
                'base_url' => $baseUrl,
            ]);

            $apiService->tokenTypes()->attach($ids);

            $this->info("✓ API сервис '{$name}' успешно добавлен");
            $this->line("  Base URL: {$baseUrl}");
            $this->line("  Поддерживаемые типы токенов:");
            foreach ($validTypes as $type) {
                $this->line("    - {$type->name}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Validate URL format.
     *
     * @param string $url
     * @return bool
     */
    protected function isValidUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * Show all API services with their supported token types.
     *
     * @return void
     */
    protected function showApiServices()
    {
        $apiServices = ApiService::with('tokenTypes')->orderBy('id')->get();

        if ($apiServices->isEmpty()) {
            $this->warn('API сервисов пока нет');
            return;
        }

        $this->info('API сервисы:');
        $this->newLine();

        foreach ($apiServices as $service) {
            $this->line("<fg=cyan>[{$service->id}] {$service->name}</>");
            $this->line("  URL: {$service->base_url}");
            $this->line("  Поддерживаемые типы токенов:");
            if ($service->tokenTypes->isEmpty()) {
                $this->line("    <fg=gray>— нет</>");
            } else {
                foreach ($service->tokenTypes as $type) {
                    $this->line("    - {$type->name}");
                }
            }
            $this->newLine();
        }
    }
}
