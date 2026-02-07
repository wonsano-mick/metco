<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Repositories\AccountTypeRepositoryInterface;
use App\Domain\Entities\AccountType;
use App\Domain\ValueObjects\Money;
use App\Models\Eloquent\AccountType as AccountTypeModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Cache;

class AccountTypeRepository implements AccountTypeRepositoryInterface
{
    private const CACHE_TTL = 600; // 10 minutes

    public function findById(UuidInterface $id): ?AccountType
    {
        $cacheKey = "account_type:{$id->toString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $model = AccountTypeModel::where('id', $id->toString())->first();

            if (!$model) {
                return null;
            }

            return $this->mapToEntity($model);
        });
    }

    public function findByCode(string $code): ?AccountType
    {
        $cacheKey = "account_type:code:{$code}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($code) {
            $model = AccountTypeModel::where('code', $code)->first();

            if (!$model) {
                return null;
            }

            return $this->mapToEntity($model);
        });
    }

    public function findAllActive(): array
    {
        $cacheKey = "account_types:active";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return AccountTypeModel::where('is_active', true)
                ->get()
                ->map(function ($model) {
                    return $this->mapToEntity($model);
                })
                ->toArray();
        });
    }

    public function save(AccountType $accountType): void
    {
        $model = AccountTypeModel::updateOrCreate(
            ['id' => $accountType->getId()->toString()],
            [
                'tenant_id' => $accountType->getTenantId()->toString(),
                'code' => $accountType->getCode(),
                'name' => $accountType->getName(),
                'description' => $accountType->getDescription(),
                'min_balance' => $accountType->getMinBalance()->getAmount(),
                'max_balance' => $accountType->getMaxBalance()->getAmount(),
                'interest_rate' => $accountType->getInterestRate(),
                'is_active' => $accountType->isActive(),
            ]
        );

        // Clear cache
        Cache::forget("account_type:{$accountType->getId()->toString()}");
        Cache::forget("account_type:code:{$accountType->getCode()}");
        Cache::forget("account_types:active");
    }

    public function delete(UuidInterface $id): void
    {
        $model = AccountTypeModel::find($id->toString());

        if ($model) {
            $model->delete();

            // Clear cache
            Cache::forget("account_type:{$id->toString()}");
            Cache::forget("account_type:code:{$model->code}");
            Cache::forget("account_types:active");
        }
    }

    private function mapToEntity(AccountTypeModel $model): AccountType
    {
        return new AccountType(
            Uuid::fromString($model->id),
            Uuid::fromString($model->tenant_id),
            $model->code,
            $model->name,
            new Money($model->min_balance, 'USD'),
            new Money($model->max_balance, 'USD'),
            (string) $model->interest_rate,
            $model->description ?? ''
        );
    }
}
