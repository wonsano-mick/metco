<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\AccountType;
use Ramsey\Uuid\UuidInterface;

interface AccountTypeRepositoryInterface
{
    public function findById(UuidInterface $id): ?AccountType;
    public function findByCode(string $code): ?AccountType;
    public function findAllActive(): array;
    public function save(AccountType $accountType): void;
    public function delete(UuidInterface $id): void;
}
