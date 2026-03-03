<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterestConfiguration extends Model
{
    use SoftDeletes;

    protected $table = 'interest_configurations';

    protected $fillable = [
        'account_type_id',
        'name',
        'code',
        'frequency',
        'interest_rate',
        'calculation_method',
        'posting_method',
        'compound_frequency_days',
        'tiers',
        'minimum_balance_required',
        'maximum_balance_limit',
        'interest_day',
        'interest_day_value',
        'is_active',
        'description',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'interest_rate' => 'decimal:4',
        'minimum_balance_required' => 'decimal:4',
        'maximum_balance_limit' => 'decimal:4',
        'compound_frequency_days' => 'integer',
        'tiers' => 'json',
        'metadata' => 'json',
        'is_active' => 'boolean',
        'interest_day_value' => 'integer',
    ];

    const FREQUENCIES = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
    const CALCULATION_METHODS = ['daily_balance', 'minimum_balance', 'average_daily_balance', 'tiered'];
    const POSTING_METHODS = ['compound', 'simple'];
    const INTEREST_DAYS = ['day_of_month', 'day_of_week', 'last_day'];

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function accountInterests(): HasMany
    {
        return $this->hasMany(AccountInterest::class);
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
