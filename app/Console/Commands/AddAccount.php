<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class AddAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:add
                          {company_id : ID компании}
                          {name : Название аккаунта}
                          {--list : Показать список аккаунтов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить новый аккаунт';

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
            $this->showAccounts();
            return 0;
        }

        $companyId = $this->argument('company_id');
        $name = trim($this->argument('name'));

        $company = Company::find($companyId);

        if (!$company) {
            $this->error("Компания с ID {$companyId} не найдена!");
            $this->newLine();
            $this->showCompanies();
            return 1;
        }

        if ($company->accounts()->where('name', $name)->exists()) {
            $this->error("Аккаунт '{$name}' уже существует у компании '{$company->name}'!");
            return 1;
        }

        try {
            $account = Account::create([
                'company_id' => $company->id,
                'name' => $name,
                'is_active' => true,
            ]);

            $this->info("✓ Аккаунт '{$name}' добавлен для компании '{$company->name}' (ID: {$account->id})");

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Show all accounts grouped by company.
     *
     * @return void
     */
    protected function showAccounts()
    {
        $companies = Company::with('accounts')->orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->warn('Компаний пока нет');
            return;
        }

        $this->info('Аккаунты по компаниям:');
        $this->newLine();

        foreach ($companies as $company) {
            $this->line("<fg=cyan>[{$company->id}] {$company->name}</>");

            if ($company->accounts->isEmpty()) {
                $this->line('  <fg=gray>— нет аккаунтов</>');
            } else {
                foreach ($company->accounts as $account) {
                    $status = $account->is_active ? '<fg=green>активен</>' : '<fg=red>неактивен</>';
                    $this->line("    [{$account->id}] {$account->name} ({$status})");
                }
            }

            $this->newLine();
        }
    }

    /**
     * Show all companies for reference.
     *
     * @return void
     */
    protected function showCompanies()
    {
        $companies = Company::orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->warn('Компаний пока нет. Создайте компанию: php artisan company:add');
            return;
        }

        $this->info('Доступные компании:');

        foreach ($companies as $company) {
            $this->line("  [{$company->id}] {$company->name}");
        }
    }
}
