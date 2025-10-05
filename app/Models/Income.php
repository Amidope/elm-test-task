<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'income_id',
        'number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'total_price',
        'date_close',
        'warehouse_name',
        'nm_id',
    ];

    protected $casts = [
        'income_id' => 'integer',
        'date' => 'datetime:Y-m-d',
        'last_change_date' => 'datetime:Y-m-d',
        'barcode' => 'bigInteger',
        'quantity' => 'integer',
        'total_price' => 'decimal:2',
        'date_close' => 'datetime:Y-m-d',
        'nm_id' => 'integer',
    ];
}
