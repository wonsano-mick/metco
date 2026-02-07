<?php

namespace App\Infrastructure\Repositories;

use Ramsey\Uuid\Uuid;
use App\Models\Eloquent\User as UserModel;
use Ramsey\Uuid\UuidInterface;
use App\Domain\ValueObjects\Email;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Domain\Repositories\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    private const CACHE_TTL = 300;

    public function findById(UuidInterface $id): ?\App\Domain\Entities\User
    {
        $cacheKey = "user:{$id->toString()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            $model = UserModel::where('id', $id->toString())->first();

            if (!$model) {
                return null;
            }

            return $this->mapToEntity($model);
        });
    }

    public function findByEmail(Email $email): ?\App\Domain\Entities\User
    {
        $cacheKey = "user:email:{$email->getValue()}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($email) {
            $model = UserModel::where('email', $email->getValue())->first();

            if (!$model) {
                return null;
            }

            return $this->mapToEntity($model);
        });
    }

    public function findByIdentifier(string $identifier): ?\App\Domain\Entities\User
    {
        // Try to find by email or phone
        $model = UserModel::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->mapToEntity($model);
    }

    public function save(\App\Domain\Entities\User $user): void
    {
        DB::transaction(function () use ($user) {
            $model = UserModel::updateOrCreate(
                ['id' => $user->getId()->toString()],
                [
                    'tenant_id' => $user->getTenantId()->toString(),
                    'email' => $user->getEmail()->getValue(),
                    'password_hash' => $user->getPasswordHash(),
                    'password_salt' => $user->getPasswordSalt(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'phone' => $user->getPhone() ? $user->getPhone()->getValue() : null,
                    'email_verified_at' => $user->isEmailVerified() ? $user->getEmailVerifiedAt() : null,
                    'phone_verified_at' => $user->isPhoneVerified() ? $user->getPhoneVerifiedAt() : null,
                    'two_factor_secret' => $user->getTwoFactorSecret(),
                    'two_factor_recovery_codes' => $user->getTwoFactorRecoveryCodes(),
                    'profile' => $user->getProfile(),
                    'is_active' => $user->isActive(),
                    'failed_login_attempts' => $user->getFailedLoginAttempts(),
                    'locked_until' => $user->getLockedUntil(),
                    'last_login_at' => $user->getLastLoginAt(),
                ]
            );

            // Update cache
            Cache::put("user:{$user->getId()->toString()}", $user, self::CACHE_TTL);
            Cache::put("user:email:{$user->getEmail()->getValue()}", $user, self::CACHE_TTL);
        });
    }

    public function delete(UuidInterface $id): void
    {
        DB::transaction(function () use ($id) {
            $model = UserModel::find($id->toString());

            if ($model) {
                $model->delete();

                // Clear cache
                Cache::forget("user:{$id->toString()}");
                Cache::forget("user:email:{$model->email}");
            }
        });
    }

    public function incrementFailedAttempts(UuidInterface $id): void
    {
        UserModel::where('id', $id->toString())
            ->increment('failed_login_attempts');

        Cache::forget("user:{$id->toString()}");
    }

    public function resetFailedAttempts(UuidInterface $id): void
    {
        UserModel::where('id', $id->toString())
            ->update(['failed_login_attempts' => 0]);

        Cache::forget("user:{$id->toString()}");
    }

    public function lockAccount(UuidInterface $id, int $minutes): void
    {
        UserModel::where('id', $id->toString())
            ->update([
                'locked_until' => now()->addMinutes($minutes),
                'failed_login_attempts' => 5, // Max attempts
            ]);

        Cache::forget("user:{$id->toString()}");
    }

    public function unlockAccount(UuidInterface $id): void
    {
        UserModel::where('id', $id->toString())
            ->update([
                'locked_until' => null,
                'failed_login_attempts' => 0,
            ]);

        Cache::forget("user:{$id->toString()}");
    }

    private function mapToEntity(UserModel $model): \App\Domain\Entities\User
    {
        $email = new Email($model->email);

        // Get first_name and last_name from model
        $firstName = $model->first_name ?? '';
        $lastName = $model->last_name ?? '';

        // Create user entity with correct parameters
        $user = new \App\Domain\Entities\User(
            Uuid::fromString($model->id),
            Uuid::fromString($model->tenant_id),
            $email,
            $model->password_hash,
            $model->password_salt,
            $firstName,
            $lastName,
            $model->profile ?? []
        );

        // Set phone if exists
        if ($model->phone) {
            $phone = new \App\Domain\ValueObjects\PhoneNumber($model->phone);
            $user->setPhone($phone);

            if ($model->phone_verified_at) {
                $user->setPhoneVerifiedAt($model->phone_verified_at);
            }
        }

        // Set email verification
        if ($model->email_verified_at) {
            $user->setEmailVerifiedAt($model->email_verified_at);
        }

        // Set 2FA if enabled
        if ($model->two_factor_secret) {
            $user->setTwoFactorProperties(
                $model->two_factor_secret,
                $model->two_factor_recovery_codes ?? []
            );
        }

        // Set account status
        if (!$model->is_active) {
            $user->setIsActive(false);
        }

        // Set failed attempts
        $user->setFailedLoginAttempts($model->failed_login_attempts);

        // Set lock status
        if ($model->locked_until) {
            $user->setLockedUntil($model->locked_until);
        }

        // Set last login
        if ($model->last_login_at) {
            $user->setLastLoginAt($model->last_login_at);
        }

        return $user;
    }
}
