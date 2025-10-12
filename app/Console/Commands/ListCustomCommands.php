<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ListCustomCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commands:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Показать список всех кастомных команд проекта';

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
        $this->info('Доступные команды:');
        $this->newLine();

        $commands = [
            'token-type:add <name>',
            'token-type:add --list',
            'company:add <name>',
            'company:add --list',
            'account:add <company_id> <account_id> <name>',
            'account:add --list',
        ];

        foreach ($commands as $command) {
            $this->line("  php artisan {$command}");
        }

        return 0;
    }
}
