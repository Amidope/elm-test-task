<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
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
        'odid',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'discount_percent' => 'integer',
        'income_id' => 'integer',
        'is_cancel' => 'boolean',
        'cancel_dt' => 'datetime',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function getDateAttribute($value)
    {
        // Коротко и безопасно: если null — вернуть null, иначе формат
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : $value;
    }

    public function getLastChangeDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : $value;
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
