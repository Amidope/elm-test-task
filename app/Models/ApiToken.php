<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class ApiToken extends Model
{
    protected $fillable = [
        'account_id',
        'api_service_id',
        'token_type_id',
        'token',
        'login',
        'password',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'token',
        'login',
        'password',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function apiService(): BelongsTo
    {
        return $this->belongsTo(ApiService::class);
    }

    public function tokenType(): BelongsTo
    {
        return $this->belongsTo(TokenType::class);
    }

    // Шифрование токена при записи
    public function setTokenAttribute($value): void
    {
        $this->attributes['token'] = Crypt::encryptString($value);
    }

    // Расшифровка токена при чтении
    public function getTokenAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Шифрование логина при записи
    public function setLoginAttribute($value): void
    {
        $this->attributes['login'] = $value ? Crypt::encryptString($value) : null;
    }

    // Расшифровка логина при чтении
    public function getLoginAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    // Шифрование пароля при записи
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    // Расшифровка пароля при чтении
    public function getPasswordAttribute($value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}
