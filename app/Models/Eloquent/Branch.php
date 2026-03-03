<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'branches';


    protected $fillable = [
        'code',
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'manager_id',
        'opening_date',
        'status',
        'working_hours',
        'settings',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'opening_date' => 'date',
        'working_hours' => 'json',
        'settings' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    // public function accounts(): HasMany
    // {
    //     return $this->hasMany(Account::class);
    // }

    /**
     * Get the customers belonging to this branch
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class, 'branch_id');
    }

     /**
     * Get all accounts belonging to this branch through customers
     * This is the correct relationship path: Branch -> Customer -> Account
     */
    public function accounts()
    {
        return $this->hasManyThrough(
            Account::class,
            Customer::class,
            'branch_id', // Foreign key on customers table
            'customer_id', // Foreign key on accounts table
            'id', // Local key on branches table
            'id' // Local key on customers table
        );
    }

     /**
     * Get active accounts for this branch
     */
    public function activeAccounts()
    {
        return $this->accounts()->where('status', 'active');
    }

    /**
     * Get the total balance of all accounts in this branch
     */
    public function getTotalBalanceAttribute()
    {
        return $this->accounts()->sum('current_balance');
    }


    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByTenant($query, string $tenantId)
    {
        // return $query->where('tenant_id', $tenantId);
    }
}
