<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemAccount extends Model
{
    use SoftDeletes;

    protected $table = 'system_accounts';

    protected $fillable = [
        'code',
        'name',
        'type',
        'currency',
        'balance',
        'is_active',
        'description',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'is_active' => 'boolean',
        'metadata' => 'json'
    ];

    // Types
    const TYPE_TELLER = 'teller';
    const TYPE_CHARGES = 'charges';
    const TYPE_INTEREST_INCOME = 'interest_income';
    const TYPE_INTEREST_EXPENSE = 'interest_expense';
    const TYPE_SUSPENSE = 'suspense';
    const TYPE_CLEARING = 'clearing';
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SystemLedgerEntry::class, 'system_account_id');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function canDebit(float $amount): bool
    {
        // Tellers need positive balance for withdrawals
        if ($this->type === self::TYPE_TELLER) {
            return $this->balance >= $amount;
        }
        // Other system accounts can go negative (income/expense)
        return true;
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
