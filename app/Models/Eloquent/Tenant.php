<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Tenant extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    // protected $primaryKey = 'id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'domain',
        'is_active',
        'settings',
    ];

    protected $casts = [
        // 'id' => 'string',
        'is_active' => 'boolean',
        'settings' => 'json',
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function accountTypes(): HasMany
    {
        return $this->hasMany(AccountType::class);
    }
}
