<?php

namespace App\Models\Eloquent;

use Ramsey\Uuid\Uuid;
use App\Traits\HasDatabaseChecks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountType extends Model
{
    use HasFactory, HasDatabaseChecks;

    protected $table = 'account_types';

    // protected $primaryKey = 'id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        // 'id',
        // 'tenant_id',
        'is_for_organizations',
        'code',
        'name',
        'description',
        'min_balance',
        'max_balance',
        'interest_rate',
        'is_active',
    ];

    protected $casts = [
        'id' => 'integer',
        // 'tenant_id' => 'string',
        'min_balance' => 'decimal:4',
        'max_balance' => 'decimal:4',
        'interest_rate' => 'decimal:4',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($model) {
    //         if (empty($model->id)) {
    //             $model->id = Uuid::uuid4()->toString();
    //         }
    //     });
    // }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function transactionLimits(): HasMany
    {
        return $this->hasMany(TransactionLimit::class);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getMinBalance(): float
    {
        return (float) $this->min_balance;
    }

    public function getMaxBalance(): float
    {
        return (float) $this->max_balance;
    }

    public function getInterestRate(): float
    {
        return (float) $this->interest_rate;
    }
}
