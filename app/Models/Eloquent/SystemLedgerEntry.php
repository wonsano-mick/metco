<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLedgerEntry extends Model
{
    protected $table = 'system_ledger_entries';

    protected $fillable = [
        'system_account_id',
        'transaction_id',
        'entry_type',
        'amount',
        'currency',
        'balance_before',
        'balance_after',
        'description',
        'metadata',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'metadata' => 'json'
    ];

    public function systemAccount(): BelongsTo
    {
        return $this->belongsTo(SystemAccount::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopeCredit($query)
    {
        return $query->where('entry_type', 'credit');
    }

    public function scopeDebit($query)
    {
        return $query->where('entry_type', 'debit');
    }
}
