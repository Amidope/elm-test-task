<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Illuminate\Console\Command;

class SyncIncomes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:incomes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync incomes with API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private SyncService $syncService)
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
        $this->syncService->sync();
        return self::SUCCESS;
    }
}
