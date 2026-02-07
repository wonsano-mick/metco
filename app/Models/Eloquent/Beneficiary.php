<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Beneficiary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'beneficiaries';

    protected $fillable = [
        // 'tenant_id',
        'customer_id',
        'created_by',
        'beneficiary_reference',
        'nickname',
        'beneficiary_type',
        'entity_type',
        'full_name',
        'business_name',
        'email',
        'phone',
        'phone_country_code',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'country',
        'postal_code',
        'bank_id',
        'bank_name',
        'bank_code',
        'branch_code',
        'branch_name',
        'account_number',
        'account_name',
        'account_type',
        'iban',
        'swift_bic',
        'routing_number',
        'sort_code',
        'ifsc_code',
        'bsb_code',
        'internal_account_id',
        'wallet_provider',
        'wallet_number',
        'bill_type',
        'bill_account_number',
        'bill_reference',
        'bill_metadata',
        'verification_status',
        'verified_at',
        'verified_by',
        'verification_notes',
        'verification_method',
        'max_transaction_amount',
        'daily_limit',
        'monthly_limit',
        'requires_2fa',
        'requires_approval',
        'total_transactions',
        'total_amount_transferred',
        'first_used_at',
        'last_used_at',
        'failed_attempts',
        'last_failed_at',
        'is_active',
        'is_favorite',
        'status',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'bill_metadata' => 'json',
        'metadata' => 'json',
        'verified_at' => 'datetime',
        'first_used_at' => 'datetime',
        'last_used_at' => 'datetime',
        'last_failed_at' => 'datetime',
        'max_transaction_amount' => 'decimal:4',
        'daily_limit' => 'decimal:4',
        'monthly_limit' => 'decimal:4',
        'total_amount_transferred' => 'decimal:4',
        'is_active' => 'boolean',
        'is_favorite' => 'boolean',
        'requires_2fa' => 'boolean',
        'requires_approval' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->beneficiary_reference)) {
    //             $model->beneficiary_reference = 'BEN' . now()->format('YmdHis') . strtoupper(Str::random(6));
    //         }
    //     });
    // }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function internalAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'internal_account_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(BeneficiaryVerificationLog::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function beneficiaryTransactions(): HasMany
    {
        return $this->hasMany(BeneficiaryTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeInternal($query)
    {
        return $query->where('beneficiary_type', 'internal');
    }

    public function scopeDomestic($query)
    {
        return $query->where('beneficiary_type', 'domestic');
    }

    public function scopeInternational($query)
    {
        return $query->where('beneficiary_type', 'international');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    // Helper Methods
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function isInternal(): bool
    {
        return $this->beneficiary_type === 'internal';
    }

    public function isDomestic(): bool
    {
        return $this->beneficiary_type === 'domestic';
    }

    public function isInternational(): bool
    {
        return $this->beneficiary_type === 'international';
    }

    public function getDisplayName(): string
    {
        return $this->nickname ?? $this->full_name ?? $this->account_name ?? $this->account_number;
    }

    public function getAccountInfo(): array
    {
        return [
            'account_number' => $this->account_number,
            'account_name' => $this->account_name,
            'bank_name' => $this->bank_name ?? $this->bank?->name,
            'bank_code' => $this->bank_code,
            'iban' => $this->iban,
            'swift_bic' => $this->swift_bic,
        ];
    }

    public function incrementUsage(float $amount): void
    {
        $this->increment('total_transactions');
        $this->increment('total_amount_transferred', $amount);
        $this->last_used_at = now();

        if (!$this->first_used_at) {
            $this->first_used_at = now();
        }

        $this->save();
    }

    public function recordFailedAttempt(): void
    {
        $this->increment('failed_attempts');
        $this->last_failed_at = now();
        $this->save();
    }

    public function verify(string $method = 'manual', User $verifier = null): void
    {
        $this->verification_status = 'verified';
        $this->verified_at = now();
        $this->verification_method = $method;

        if ($verifier) {
            $this->verified_by = $verifier->id;
        }

        $this->save();
    }

    public function markAsFailed(string $notes = null): void
    {
        $this->verification_status = 'failed';
        $this->verification_notes = $notes;
        $this->save();
    }

    public function toggleFavorite(): bool
    {
        $this->is_favorite = !$this->is_favorite;
        return $this->save();
    }

    public function isWithinLimits(float $amount): bool
    {
        // Check per-transaction limit
        if ($this->max_transaction_amount && $amount > $this->max_transaction_amount) {
            return false;
        }

        // Check daily limit
        if ($this->daily_limit) {
            $dailyTotal = Transaction::where('beneficiary_id', $this->id)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('amount');

            if (($dailyTotal + $amount) > $this->daily_limit) {
                return false;
            }
        }

        // Check monthly limit
        if ($this->monthly_limit) {
            $monthlyTotal = Transaction::where('beneficiary_id', $this->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'completed')
                ->sum('amount');

            if (($monthlyTotal + $amount) > $this->monthly_limit) {
                return false;
            }
        }

        return true;
    }
}
