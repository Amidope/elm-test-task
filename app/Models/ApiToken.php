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

    // Шифрование токена
    public function setTokenAttribute($value): void
    {
        $this->attributes['token'] = Crypt::encryptString($value);
    }

    public function getDecryptedTokenAttribute(): string
    {
        return Crypt::decryptString($this->attributes['token']);
    }

    // Шифрование логина
    public function setLoginAttribute($value): void
    {
        $this->attributes['login'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedLoginAttribute(): ?string
    {
        return $this->attributes['login'] ? Crypt::decryptString($this->attributes['login']) : null;
    }

    // Шифрование пароля
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedPasswordAttribute(): ?string
    {
        return $this->attributes['password'] ? Crypt::decryptString($this->attributes['password']) : null;
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