<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'g_number',
        'nm_id',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'total_price',
        'discount_percent',
        'warehouse_name',
        'oblast',
        'income_id',
        'subject',
        'category',
        'brand',
        'is_cancel',
        'cancel_dt',
        'odid'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
