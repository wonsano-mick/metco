<?php

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entity that owns the audit log.
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Scope query to filter by entity.
     */
    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);

        if ($entityId) {
            $query->where('entity_id', $entityId);
        }

        return $query;
    }

    /**
     * Scope query to filter by action.
     */
    public function scopeWithAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get readable action name.
     */
    public function getReadableActionAttribute(): string
    {
        return str_replace('_', ' ', ucfirst($this->action));
    }

    /**
     * Get formatted old values for display.
     */
    public function getFormattedOldValuesAttribute(): array
    {
        return $this->formatValues($this->old_values);
    }

    /**
     * Get formatted new values for display.
     */
    public function getFormattedNewValuesAttribute(): array
    {
        return $this->formatValues($this->new_values);
    }

    /**
     * Format values for display.
     */
    private function formatValues(?array $values): array
    {
        if (!$values) {
            return [];
        }

        $formatted = [];
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $formatted[$key] = json_encode($value, JSON_PRETTY_PRINT);
            } else {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }
}
