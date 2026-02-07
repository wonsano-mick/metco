<?php

namespace App\Models\Eloquent;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;
    use HasRoles; // Make sure this comes after other traits

    // IMPORTANT: This must be 'web' for Spatie Permissions
    protected $guard_name = 'web';

    protected $table = 'users';

    // public $incrementing = true;
    // protected $keyType = 'int';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone', 
        'username',
        'password',
        'role',
        'status',
        'email_verified_at',
        'last_login_at',
        'password_changed_at',
        'profile_photo_path',
        'two_factor_enabled',
        'two_factor_secret',
        'login_attempts',
        'locked_until',
        'branch_id',
        // 'tenant_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        // 'hire_date' => 'datetime',
        'locked_until' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'login_attempts' => 'integer',
        // 'date_of_birth' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Auto-assign role based on the role attribute
            if ($user->role) {
                try {
                    $user->assignRole($user->role);
                } catch (\Exception $e) {
                    // Log error but don't break
                    Log::warning("Failed to assign role {$user->role} to user {$user->id}: " . $e->getMessage());
                }
            }
        });

        static::updated(function ($user) {
            // Sync role if role attribute changed
            if ($user->isDirty('role')) {
                try {
                    $user->syncRoles([$user->role]);
                } catch (\Exception $e) {
                    Log::warning("Failed to sync role {$user->role} for user {$user->id}: " . $e->getMessage());
                }
            }
        });
    }

    // Relationships

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Role check methods - SIMPLIFIED VERSION
    public function isAdmin(): bool
    {
        // Simple check - just look at the role attribute
        return in_array($this->role, ['super-admin', 'admin']);
    }

    public function isBranchManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isTeller(): bool
    {
        return $this->role === 'teller';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    // Status methods
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until > now();
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        });
    }
}
