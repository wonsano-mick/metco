<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $table = 'ledger_entries';

    // Make sure these match your actual database columns
    protected $fillable = [
        'transaction_id',
        'account_id',
        'entry_type',
        'category',
        'amount',
        'running_balance',
        'balance_after',
        'available_balance_after',
        'ledger_balance_after',
        'currency',
        'balance_before',
        'available_balance_before',
        'ledger_balance_before',
        'description',
        'metadata',
        'reversal_data',
        'is_settled',
        'settled_at',
        'settlement_reference',
        'is_reversed',
        'reversed_by',
        'reversed_at',
        'reversal_entry_id',
        'created_by',
        'updated_by',
        'entry_date',
        'value_date',
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'account_id' => 'integer',
        'amount' => 'decimal:4',
        'running_balance' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'available_balance_after' => 'decimal:4',
        'ledger_balance_after' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'available_balance_before' => 'decimal:4',
        'ledger_balance_before' => 'decimal:4',
        'metadata' => 'json',
        'reversal_data' => 'json',
        'is_settled' => 'boolean',
        'is_reversed' => 'boolean',
        'settled_at' => 'datetime',
        'reversed_at' => 'datetime',
        'entry_date' => 'datetime',
        'value_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Remove the commented-out UUID code since your CSV shows integer IDs

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    // Remove tenant() method if you don't have tenant_id column
    // public function tenant(): BelongsTo
    // {
    //     return $this->belongsTo(Tenant::class);
    // }

    public function scopeCredit($query)
    {
        return $query->where('entry_type', 'credit');
    }

    public function scopeDebit($query)
    {
        return $query->where('entry_type', 'debit');
    }

    public function scopeByAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeByTransaction($query, string $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    // Remove ByTenant scope if no tenant_id
    // public function scopeByTenant($query, string $tenantId)
    // {
    //     return $query->where('tenant_id', $tenantId);
    // }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
