<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountMonthlyProcessing extends Model
{
    use HasFactory;

    protected $table = 'account_monthly_processing';

    protected $fillable = [
        'account_id',
        'account_type_id',
        'monthly_fee_applied',
        'interest_earned',
        'balance_before',
        'balance_after',
        'processing_month',
        'processed_at',
        'metadata'
    ];

    protected $casts = [
        'monthly_fee_applied' => 'decimal:4',
        'interest_earned' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
        'processing_month' => 'date',
        'processed_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
}
