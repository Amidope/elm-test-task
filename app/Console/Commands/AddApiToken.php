<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use App\Models\Account;
use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-token:add
                          {account_id? : ID аккаунта}
                          {api_service? : Название API сервиса}
                          {token_type? : Тип токена}
                          {token? : Токен для API}
                          {--login= : Логин для Basic Auth}
                          {--password= : Пароль для Basic Auth}
                          {--list : Показать список токенов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить API токен для аккаунта';

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
            $this->showApiTokens();
            return 0;
        }

        $accountId = $this->argument('account_id');
        $apiServiceName = $this->argument('api_service');
        $tokenTypeName = $this->argument('token_type');
        $token = $this->argument('token');
        $login = $this->option('login');
        $password = $this->option('password');

        if (!$accountId) {
            $this->error('Укажите ID аккаунта');
            return 1;
        }

        if (!$apiServiceName) {
            $this->error('Укажите название API сервиса');
            return 1;
        }

        if (!$tokenTypeName) {
            $this->error('Укажите тип токена');
            return 1;
        }

        if (!$token) {
            $this->error('Укажите токен');
            return 1;
        }

        $apiServiceName = strtolower(trim($apiServiceName));
        $tokenTypeName = strtolower(trim($tokenTypeName));

        $account = Account::find($accountId);
        if (!$account) {
            $this->error("Аккаунт с ID {$accountId} не найден!");
            $this->newLine();
            $this->showAccounts();
            return 1;
        }

        $apiService = ApiService::where('name', $apiServiceName)->first();
        if (!$apiService) {
            $this->error("API сервис '{$apiServiceName}' не найден!");
            $this->newLine();
            $this->showApiServices();
            return 1;
        }

        $tokenType = TokenType::where('name', $tokenTypeName)->first();
        if (!$tokenType) {
            $this->error("Тип токена '{$tokenTypeName}' не найден!");
            $this->newLine();
            $this->showTokenTypes();
            return 1;
        }

        if (!$apiService->tokenTypes()->where('token_types.id', $tokenType->id)->exists()) {
            $this->error("API сервис '{$apiServiceName}' не поддерживает тип токена '{$tokenTypeName}'!");
            $supportedTypes = $apiService->tokenTypes->pluck('name')->join(', ');
            $this->line("Поддерживаемые типы: {$supportedTypes}");
            return 1;
        }

        $existingToken = $account->apiTokens()
            ->where('api_service_id', $apiService->id)
            ->where('token_type_id', $tokenType->id)
            ->first();

        if ($existingToken) {
            $this->error("У аккаунта '{$account->name}' уже существует токен типа '{$tokenTypeName}' для сервиса '{$apiServiceName}'!");
            $this->line("Один аккаунт может иметь только один токен каждого типа для каждого сервиса.");
            return 1;
        }

        if ($tokenTypeName === 'basic' && (!$login || !$password)) {
            $this->error("Для типа токена 'basic' необходимо указать --login и --password!");
            return 1;
        }

        try {
            $apiToken = ApiToken::create([
                'account_id' => $account->id,
                'api_service_id' => $apiService->id,
                'token_type_id' => $tokenType->id,
                'token' => $token,
                'login' => $login,
                'password' => $password,
                'is_active' => true,
            ]);

            $this->info("✓ API токен добавлен для аккаунта '{$account->name}'");
            $this->line("  Сервис: {$apiService->name}");
            $this->line("  Тип: {$tokenType->name}");
            if ($login) {
                $this->line("  Логин: {$login}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Show all API tokens grouped by account.
     *
     * @return void
     */
    protected function showApiTokens()
    {
        $accounts = Account::with(['apiTokens.apiService', 'apiTokens.tokenType', 'company'])
            ->has('apiTokens')
            ->orderBy('id')
            ->get();

        if ($accounts->isEmpty()) {
            $this->warn('API токенов пока нет');
            return;
        }

        $this->info('API токены по аккаунтам:');
        $this->newLine();

        foreach ($accounts as $account) {
            $this->line("<fg=cyan>[{$account->id}] {$account->name} ({$account->company->name})</>");

            foreach ($account->apiTokens as $apiToken) {
                $status = $apiToken->is_active ? '<fg=green>активен</>' : '<fg=red>неактивен</>';
                $this->line("    [{$apiToken->id}] {$apiToken->apiService->name} / {$apiToken->tokenType->name} ({$status})");
                if ($apiToken->login) {
                    $this->line("        Логин: {$apiToken->login}");
                }
            }

            $this->newLine();
        }
    }

    /**
     * Show all accounts for reference.
     *
     * @return void
     */
    protected function showAccounts()
    {
        $accounts = Account::with('company')->orderBy('id')->get();

        if ($accounts->isEmpty()) {
            $this->warn('Аккаунтов пока нет');
            return;
        }

        $this->info('Доступные аккаунты:');

        foreach ($accounts as $account) {
            $this->line("  [{$account->id}] {$account->name} ({$account->company->name})");
        }
    }

    /**
     * Show all API services for reference.
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

        $this->info('Доступные API сервисы:');

        foreach ($apiServices as $service) {
            $types = $service->tokenTypes->pluck('name')->join(', ');
            $this->line("  - {$service->name} (типы: {$types})");
        }
    }

    /**
     * Show all token types for reference.
     *
     * @return void
     */
    protected function showTokenTypes()
    {
        $tokenTypes = TokenType::orderBy('id')->get();

        if ($tokenTypes->isEmpty()) {
            $this->warn('Типов токенов пока нет');
            return;
        }

        $this->info('Доступные типы токенов:');

        foreach ($tokenTypes as $type) {
            $this->line("  - {$type->name}");
        }
    }
}
