<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class UserSession extends Model
{
    use HasFactory;

    protected $table = 'user_sessions';

    // protected $primaryKey = 'id';
    // public $incrementing = false;
    // protected $keyType = 'string';

    protected $fillable = [
        // 'id',
        'user_id',
        'device_id',
        'device_info',
        'ip_address',
        'user_agent',
        'access_token_hash',
        'refresh_token_hash',
        'expires_at',
        'revoked_at',
        'last_activity_at',
    ];

    protected $casts = [
        // 'id' => 'string',
        'user_id' => 'integer',
        'device_info' => 'json',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Uuid::uuid4()->toString();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return !$this->revoked_at && $this->expires_at > now();
    }

    public function revoke(): void
    {
        $this->revoked_at = now();
        $this->save();
    }
}
