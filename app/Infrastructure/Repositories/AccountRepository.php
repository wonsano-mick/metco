<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\AccountRepositoryInterface;
use App\Domain\Entities\Account;
use App\Domain\ValueObjects\AccountNumber;
use App\Domain\ValueObjects\Money;
use App\Models\Eloquent\Account as AccountModel;
use App\Models\Eloquent\LedgerEntry as LedgerEntryModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AccountRepository implements AccountRepositoryInterface
{
    private const CACHE_TTL = 300;

    public function findById(UuidInterface $id): ?Account
    {
        $cacheKey = "account:{$id->toString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $model = AccountModel::with(['accountType', 'user'])
                ->where('id', $id->toString())
                ->first();

            if (!$model) {
                return null;
            }

            return $this->mapToEntity($model);
        });
    }

    public function findByAccountNumber(AccountNumber $accountNumber): ?Account
    {
        $cacheKey = "account:number:{$accountNumber->getNumber()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($accountNumber) {
            $model = AccountModel::with(['accountType', 'user'])
                ->where('account_number', $accountNumber->getNumber())
                ->first();

            if (!$model) {
                return null;
            }

            return $this->mapToEntity($model);
        });
    }

    public function findAndLock(UuidInterface $id): ?Account
    {
        $model = AccountModel::with(['accountType', 'user'])
            ->where('id', $id->toString())
            ->lockForUpdate()
            ->first();

        if (!$model) {
            return null;
        }

        // Clear cache since we're updating
        Cache::forget("account:{$id->toString()}");
        Cache::forget("account:number:{$model->account_number}");

        return $this->mapToEntity($model);
    }

    public function save(Account $account): void
    {
        DB::transaction(function () use ($account) {
            $model = AccountModel::updateOrCreate(
                ['id' => $account->getId()->toString()],
                [
                    'tenant_id' => $account->getTenantId()->toString(),
                    'user_id' => $account->getUserId()->toString(),
                    'account_number' => $account->getAccountNumber()->getNumber(),
                    'account_type_id' => $account->getAccountTypeId()->toString(),
                    'current_balance' => $account->getCurrentBalance()->getAmount(),
                    'available_balance' => $account->getAvailableBalance()->getAmount(),
                    'ledger_balance' => $account->getLedgerBalance()->getAmount(),
                    'currency' => $account->getCurrency(),
                    'status' => $account->getStatus(),
                    'opened_at' => $account->getOpenedAt(),
                    'closed_at' => $account->getClosedAt(),
                ]
            );

            // Update cache
            Cache::put("account:{$account->getId()->toString()}", $account, self::CACHE_TTL);
            Cache::put(
                "account:number:{$account->getAccountNumber()->getNumber()}",
                $account,
                self::CACHE_TTL
            );
        });
    }

    public function delete(UuidInterface $id): void
    {
        DB::transaction(function () use ($id) {
            $model = AccountModel::find($id->toString());

            if ($model) {
                $model->delete();

                // Clear cache
                Cache::forget("account:{$id->toString()}");
                Cache::forget("account:number:{$model->account_number}");
            }
        });
    }

    public function getUserAccounts(UuidInterface $userId): array
    {
        return AccountModel::where('user_id', $userId->toString())
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($model) {
                return $this->mapToEntity($model);
            })
            ->toArray();
    }

    public function getTransactions(
        UuidInterface $accountId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $page = 1,
        int $perPage = 50
    ): array {
        $ledgerEntries = LedgerEntryModel::with(['transaction'])
            ->where('account_id', $accountId->toString())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page)
            ->items();

        return array_map(function ($entry) {
            return [
                'id' => $entry->id,
                'transaction_id' => $entry->transaction_id,
                'entry_type' => $entry->entry_type,
                'amount' => $entry->amount,
                'currency' => $entry->currency,
                'balance_after' => $entry->balance_after,
                'created_at' => $entry->created_at,
                'transaction' => [
                    'reference' => $entry->transaction->transaction_reference ?? null,
                    'type' => $entry->transaction->type ?? null,
                    'description' => $entry->transaction->description ?? null,
                    'status' => $entry->transaction->status ?? null,
                ],
            ];
        }, $ledgerEntries);
    }

    public function getTransactionSummary(
        UuidInterface $accountId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $summary = LedgerEntryModel::where('account_id', $accountId->toString())
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("
                SUM(CASE WHEN entry_type = 'credit' THEN amount ELSE 0 END) as total_credits,
                SUM(CASE WHEN entry_type = 'debit' THEN amount ELSE 0 END) as total_debits,
                COUNT(DISTINCT transaction_id) as transaction_count
            ")
            ->first();

        return [
            'totalCredits' => $summary->total_credits ?? '0',
            'totalDebits' => $summary->total_debits ?? '0',
            'transactionCount' => $summary->transaction_count ?? 0,
            'netChange' => bcsub(
                $summary->total_credits ?? '0',
                $summary->total_debits ?? '0',
                4
            ),
        ];
    }

    private function mapToEntity(AccountModel $model): Account
    {
        $accountNumber = new AccountNumber($model->account_number);

        return new Account(
            Uuid::fromString($model->id),
            Uuid::fromString($model->tenant_id),
            Uuid::fromString($model->user_id),
            $accountNumber,
            Uuid::fromString($model->account_type_id),
            new Money($model->current_balance, $model->currency),
            $model->currency
        );
    }
}
