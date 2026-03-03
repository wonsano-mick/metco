<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'accounts';

    protected $fillable = [
        'customer_id',
        'account_type_id',
        'account_number',
        'currency',
        'current_balance',
        'available_balance',
        'ledger_balance',
        'initial_deposit',
        'minimum_balance',
        'overdraft_limit',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'account_type_id' => 'integer',
        'current_balance' => 'decimal:4',
        'available_balance' => 'decimal:4',
        'ledger_balance' => 'decimal:4',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Get transactions where this account is the source
     */
    public function sourceTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'source_account_id');
    }

    /**
     * Get transactions where this account is the destination
     */
    public function destinationTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'destination_account_id');
    }

    /**
     * Get all transactions related to this account (either as source or destination)
     * This uses a custom query instead of a relationship to avoid the account_id issue
     */
    public function getAllTransactionsAttribute()
    {
        return Transaction::where('source_account_id', $this->id)
            ->orWhere('destination_account_id', $this->id);
    }

    /**
     * Get count of all transactions for this account
     */
    public function getTransactionsCountAttribute()
    {
        return Transaction::where('source_account_id', $this->id)
            ->orWhere('destination_account_id', $this->id)
            ->whereIn('status', ['completed', 'posted'])
            ->count();
    }

    /**
     * Get sum of all transaction amounts for this account
     */
    public function getTransactionsTotalAttribute()
    {
        return Transaction::where('source_account_id', $this->id)
            ->orWhere('destination_account_id', $this->id)
            ->whereIn('status', ['completed', 'posted'])
            ->sum('amount');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFrozen(): bool
    {
        return $this->status === 'frozen';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFrozen($query)
    {
        return $query->where('status', 'frozen');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}