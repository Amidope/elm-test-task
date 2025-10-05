<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all sync commands';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private array $commands)
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
        foreach ($this->commands as $command) {
            $class = class_basename($command);
            $this->info("Запуск {$class}...");
            $command->handle();
        }

        $this->info('Все синхронизации завершены.');
        return self::SUCCESS;
    }
}
