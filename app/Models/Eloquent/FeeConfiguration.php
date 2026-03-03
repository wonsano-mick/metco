<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeConfiguration extends Model
{
    use SoftDeletes;

    protected $table = 'fee_configurations';

    protected $fillable = [
        'account_type_id',
        'name',
        'code',
        'frequency',
        'fee_amount',
        'currency',
        'calculation_method',
        'percentage_rate',
        'tiers',
        'has_minimum_balance_waiver',
        'minimum_balance_threshold',
        'charge_day',
        'charge_day_value',
        'is_active',
        'description',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:4',
        'percentage_rate' => 'decimal:4',
        'minimum_balance_threshold' => 'decimal:4',
        'tiers' => 'json',
        'metadata' => 'json',
        'has_minimum_balance_waiver' => 'boolean',
        'is_active' => 'boolean',
        'charge_day_value' => 'integer',
    ];

    const FREQUENCIES = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
    const CALCULATION_METHODS = ['fixed', 'percentage', 'tiered'];
    const CHARGE_DAYS = ['day_of_month', 'day_of_week', 'last_day'];

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function accountFees(): HasMany
    {
        return $this->hasMany(AccountFee::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByAccountType($query, $accountTypeId)
    {
        return $query->where('account_type_id', $accountTypeId);
    }
}
