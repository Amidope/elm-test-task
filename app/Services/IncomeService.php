<?php

namespace App\Services;

use App\Contracts\ApiDataHandlerInterface;
use App\Models\Income;

class IncomeService implements ApiDataHandlerInterface
{

    public function __construct()
    {
    }
    public function handleData(array $data): void
    {
        Income::upsert($data, ['account_id', 'income_id', 'nm_id']);
    }
}
