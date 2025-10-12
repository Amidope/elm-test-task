<?php

namespace App\Console\Commands;

use App\Models\TokenType;
use Illuminate\Console\Command;

class AddTokenType extends Command
{
    /**
     *
     * {name} - обязательный аргумент (имя типа токена)
     * {--list} - опциональный флаг для вывода списка существующих типов
     * @var string
     */
    protected $signature = 'token-type:add
                          {name : Название типа токена}
                          {--list : Показать список существующих типов}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Добавить новый тип токена в систему';

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
            $this->showTokenTypes();
            return 0;
        }

        $name = strtolower(trim($this->argument('name')));

        if (TokenType::where('name', $name)->exists()) {
            $this->error("Ошибка: Тип токена '{$name}' уже существует!");
            $this->newLine();
            $this->showTokenTypes();
            return 1;
        }

        try {
            TokenType::create(['name' => $name]);
            $this->info("✓ Тип токена '{$name}' успешно добавлен");

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка: {$e->getMessage()}");
            return 1;
        }
    }

    protected function showTokenTypes()
    {
        $tokenTypes = TokenType::orderBy('id')->get();

        if ($tokenTypes->isEmpty()) {
            $this->warn('Типов токенов пока нет');
            return;
        }

        $this->info('Типы токенов:');

        foreach ($tokenTypes as $type) {
            $this->line("  - {$type->name}");
        }
    }

}
