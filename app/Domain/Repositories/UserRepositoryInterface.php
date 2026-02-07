<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Email;
use Ramsey\Uuid\UuidInterface;

interface UserRepositoryInterface
{
    public function findById(UuidInterface $id): ?User;
    public function findByEmail(Email $email): ?User;
    public function findByIdentifier(string $identifier): ?User;
    public function save(User $user): void;
    public function delete(UuidInterface $id): void;
    public function incrementFailedAttempts(UuidInterface $id): void;
    public function resetFailedAttempts(UuidInterface $id): void;
    public function lockAccount(UuidInterface $id, int $minutes): void;
    public function unlockAccount(UuidInterface $id): void;
}
