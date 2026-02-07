<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class TransactionLimit extends Model
{
    use HasFactory;

    protected $table = 'transaction_limits';

    // protected $primaryKey = 'id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        // 'id',
        // 'tenant_id',
        'account_type_id',
        'period',
        'transaction_type',
        'max_amount',
        'max_count',
        'is_active',
    ];

    protected $casts = [
        // 'id' => 'string',
        // 'tenant_id' => 'string',
        'account_type_id' => 'integer',
        'max_amount' => 'decimal:4',
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

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
