<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLimit extends Model
{
    use HasFactory;

    protected $table = 'transaction_limits';


    protected $fillable = [
  
        'account_type_id',
        'period',
        'transaction_type',
        'max_amount',
        'max_count',
        'is_active',
    ];

    protected $casts = [

        'account_type_id' => 'integer',
        'max_amount' => 'decimal:4',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
