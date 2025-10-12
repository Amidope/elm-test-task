<?php

namespace App\Contracts;

interface ApiDataHandlerInterface
{
    public function handleData(array $data): void;
}
