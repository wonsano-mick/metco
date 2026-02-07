<?php

namespace App\Infrastructure\Repositories;

use Ramsey\Uuid\Uuid;
use App\Models\Eloquent\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuditLogRepository
{
    public function log(array $data): void
    {
        DB::connection('audit')->table('audit_logs')->insert([
            'id' => Uuid::uuid4()->toString(),
            'tenant_id' => $data['tenant_id'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'action' => $data['action'],
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'old_values' => json_encode($data['old_values'] ?? null),
            'new_values' => json_encode($data['new_values'] ?? null),
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'metadata' => json_encode($data['metadata'] ?? null),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Also log to local file for development
        if (app()->environment('local')) {
            Log::channel('audit')->info('Audit log', $data);
        }
    }
}
