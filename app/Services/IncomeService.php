<?php

namespace App\Services;

use App\Models\Income;

class IncomeService implements ApiDataHandlerInterface
{

    public function __construct(private Income $income)
    {
    }
    public function handleData(array $data): void
    {
        $this->income::upsert($data, ['income_id', 'nm_id']);
    }
}
