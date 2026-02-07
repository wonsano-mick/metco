<?php

namespace App\Services;

use App\Models\Eloquent\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an action.
     */
    public static function log(string $action, Model $entity, ?array $oldValues = null, ?array $newValues = null, ?array $metadata = null): AuditLog
    {
        $userId = Auth::id();

        $logData = [
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ];

        return AuditLog::create($logData);
    }

    /**
     * Log transaction creation.
     */
    public static function logTransactionCreated($transaction, ?array $metadata = null): AuditLog
    {
        $newValues = [
            'transaction_reference' => $transaction->transaction_reference,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'description' => $transaction->description,
            'source_account_id' => $transaction->source_account_id,
            'destination_account_id' => $transaction->destination_account_id,
            'beneficiary_id' => $transaction->beneficiary_id,
        ];

        return self::log('transaction_created', $transaction, null, $newValues, $metadata);
    }

    /**
     * Log transaction status change.
     */
    public static function logTransactionStatusChange($transaction, string $oldStatus, string $newStatus, ?array $metadata = null): AuditLog
    {
        $oldValues = ['status' => $oldStatus];
        $newValues = ['status' => $newStatus];

        $action = 'transaction_status_changed';

        // Specific actions for common status changes
        if ($newStatus === 'completed') {
            $action = 'transaction_completed';
        } elseif ($newStatus === 'failed') {
            $action = 'transaction_failed';
        } elseif ($newStatus === 'reversed') {
            $action = 'transaction_reversed';
        } elseif ($newStatus === 'cancelled') {
            $action = 'transaction_cancelled';
        }

        return self::log($action, $transaction, $oldValues, $newValues, $metadata);
    }

    /**
     * Log transaction update.
     */
    public static function logTransactionUpdated($transaction, array $changes, ?array $metadata = null): AuditLog
    {
        $oldValues = [];
        $newValues = [];

        foreach ($changes as $field => $change) {
            $oldValues[$field] = $change['old'];
            $newValues[$field] = $change['new'];
        }

        return self::log('transaction_updated', $transaction, $oldValues, $newValues, $metadata);
    }

    /**
     * Log transaction verification.
     */
    public static function logTransactionVerified($transaction, ?array $metadata = null): AuditLog
    {
        $newValues = [
            'verified_at' => now(),
            'verified_by' => Auth::id(),
        ];

        return self::log('transaction_verified', $transaction, null, $newValues, $metadata);
    }

    /**
     * Get audit logs for a transaction.
     */
    public static function getTransactionLogs($transactionId)
    {
        return AuditLog::where('entity_type', \App\Models\Eloquent\Transaction::class)
            ->where('entity_id', $transactionId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
