<?php

use App\Models\Account;

if (!function_exists('arraysAreEqualStrict')) {
    /**
     * Строго сравнивает два массива после сортировки по ключам.
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    function arraysAreEqualStrict(array $a, array $b): bool
    {
        ksort($a);
        ksort($b);
        return $a === $b;
    }
}


if (!function_exists('rejectSaved')) {
    /**
     * @param array $incoming Массив новых записей из API
     * @param array $fromDb Массив существующих записей из БД
     * @return array Массив записей, которых нет в БД
     */
    function rejectSaved(array $incoming, array $fromDb): array
    {
        foreach ($fromDb as $dbItem) {
            foreach ($incoming as $index => $incomingItem) {
                if (arraysAreEqualStrict($incomingItem, $dbItem)) {
                    unset($incoming[$index]);
                    break;
                }
            }
        }
        return $incoming;
    }
}

if (!function_exists('addAccountId')) {
    /**
     * Добавить account_id к каждому элементу.
     *
     * @param array $data
     * @param Account $account
     * @return array
     */
    function addAccountId(array $data, Account $account): array
    {
        return array_map(function ($item) use ($account) {
            $item['account_id'] = $account->id;
            return $item;
        }, $data);
    }


if (!function_exists('normalizeOrdersForCompare')) {
    /**
     * Нормализует массив заказов для строгого сравнения.
     * Приводит total_price к единому формату с 2 знаками после точки и сортирует ключи.
     *
     * @param array $orders Массив заказов
     * @return array Нормализованный массив заказов
     */
    function normalizeOrdersForCompare(array $orders): array
    {
        return array_map(function($item) {
            if (isset($item['total_price'])) {
                $item['total_price'] = number_format((float)$item['total_price'], 2, '.', '');
            }
            ksort($item);
            return $item;
        }, $orders);
    }
}

}
