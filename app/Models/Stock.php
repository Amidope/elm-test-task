<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'quantity',
        'is_supply',
        'is_realization',
        'quantity_full',
        'warehouse_name',
        'in_way_to_client',
        'in_way_from_client',
        'nm_id',
        'subject',
        'category',
        'brand',
        'sc_code',
        'price',
        'discount',
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
        'last_change_date' => 'datetime:Y-m-d',
        'barcode' => 'bigInteger',
        'quantity' => 'integer',
        'is_supply' => 'boolean',
        'is_realization' => 'boolean',
        'quantity_full' => 'integer',
        'in_way_to_client' => 'integer',
        'in_way_from_client' => 'integer',
        'nm_id' => 'integer',
        'sc_code' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
