<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class AddCompany extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:add
                          {name? : Название компании}
                          {--list : Показать список компаний}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить новую компанию';

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
            $this->showCompanies();
            return 0;
        }

        $name = $this->argument('name');

        if (!$name) {
            $this->error('Укажите название компании');
            return 1;
        }

        $name = trim($name);

        if (Company::where('name', $name)->exists()) {
            $this->error("Ошибка: Компания '{$name}' уже существует!");
            $this->newLine();
            $this->showCompanies();
            return 1;
        }

        try {
            $company = Company::create(['name' => $name]);
            $this->info("✓ Компания '{$name}' успешно добавлена (ID: {$company->id})");

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Show all companies.
     *
     * @return void
     */
    protected function showCompanies()
    {
        $companies = Company::orderBy('id')->get();

        if ($companies->isEmpty()) {
            $this->warn('Компаний пока нет');
            return;
        }

        $this->info('Компании:');

        foreach ($companies as $company) {
            $accountsCount = $company->accounts()->count();
            $this->line("  [{$company->id}] {$company->name} (аккаунтов: {$accountsCount})");
        }
    }
}
