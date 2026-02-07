<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\TransactionRepositoryInterface;
use App\Domain\Entities\Transaction;
use App\Domain\ValueObjects\Money;
use App\Models\Eloquent\Transaction as TransactionModel;
use App\Models\Eloquent\LedgerEntry as LedgerEntryModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\DB;

class TransactionRepository implements TransactionRepositoryInterface
{ 
    public function findById(UuidInterface $id): ?Transaction
    {
        $model = TransactionModel::where('id', $id->toString())->first();

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function save(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $model = TransactionModel::updateOrCreate(
                ['id' => $transaction->getId()->toString()],
                [
                    'tenant_id' => $transaction->getTenantId()->toString(),
                    'transaction_reference' => $transaction->getReference(),
                    'type' => $transaction->getType(),
                    'status' => $transaction->getStatus(),
                    'amount' => $transaction->getAmount()->getAmount(),
                    'currency' => $transaction->getAmount()->getCurrency(),
                    'description' => $transaction->getDescription(),
                    'metadata' => $transaction->getMetadata(),
                    'initiated_by' => $transaction->getInitiatedBy()->toString(),
                    'initiated_at' => $transaction->getInitiatedAt(),
                    'completed_at' => $transaction->getCompletedAt(),
                ]
            );
        });
    }

    public function createDeposit(
        Money $amount,
        string $description,
        string $initiatedBy,
        UuidInterface $tenantId
    ): Transaction {
        return new Transaction(
            Uuid::uuid4(),
            $tenantId,
            'deposit',
            $amount,
            $description,
            Uuid::fromString($initiatedBy)
        );
    }

    public function createWithdrawal(
        Money $amount,
        string $description,
        string $initiatedBy,
        UuidInterface $tenantId
    ): Transaction {
        return new Transaction(
            Uuid::uuid4(),
            $tenantId,
            'withdrawal',
            $amount,
            $description,
            Uuid::fromString($initiatedBy)
        );
    }

    public function createTransfer(
        Money $amount,
        string $description,
        string $initiatedBy,
        UuidInterface $tenantId
    ): Transaction {
        return new Transaction(
            Uuid::uuid4(),
            $tenantId,
            'transfer',
            $amount,
            $description,
            Uuid::fromString($initiatedBy)
        );
    }

    public function createLedgerEntry(
        UuidInterface $transactionId,
        UuidInterface $accountId,
        string $entryType,
        Money $amount,
        Money $balanceAfter
    ): void {
        DB::transaction(function () use ($transactionId, $accountId, $entryType, $amount, $balanceAfter) {
            // Get tenant ID from transaction
            $transaction = TransactionModel::find($transactionId->toString());

            if (!$transaction) {
                throw new \Exception('Transaction not found');
            }

            LedgerEntryModel::create([
                'id' => Uuid::uuid4()->toString(),
                'tenant_id' => $transaction->tenant_id,
                'transaction_id' => $transactionId->toString(),
                'account_id' => $accountId->toString(),
                'entry_type' => $entryType,
                'amount' => $amount->getAmount(),
                'currency' => $amount->getCurrency(),
                'balance_after' => $balanceAfter->getAmount(),
            ]);
        });
    }

    private function mapToEntity(TransactionModel $model): Transaction
    {
        return new Transaction(
            Uuid::fromString($model->id),
            Uuid::fromString($model->tenant_id),
            $model->type,
            new Money($model->amount, $model->currency),
            $model->description,
            Uuid::fromString($model->initiated_by),
            $model->transaction_reference,
            $model->status,
            $model->metadata,
            $model->initiated_at,
            $model->completed_at
        );
    }
}
