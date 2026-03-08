<?php

namespace App\Models\Eloquent;

use App\Models\Eloquent\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'balance_date',
        'opening_balance',
        'closing_balance',
    ];

    protected $casts = [
        'balance_date' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    /**
     * Relationship: Daily balance belongs to an account
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
