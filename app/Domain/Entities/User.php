<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\PhoneNumber;
use App\Domain\ValueObjects\Address;
use Ramsey\Uuid\UuidInterface;
use DateTimeImmutable;

class User
{
    private UuidInterface $id;
    private UuidInterface $tenantId;
    private Email $email;
    private ?PhoneNumber $phone;
    private string $passwordHash;
    private string $passwordSalt;
    private ?Address $address;
    private bool $emailVerified;
    private bool $phoneVerified;
    private bool $twoFactorEnabled;
    private ?string $twoFactorSecret;
    private array $twoFactorRecoveryCodes;
    private bool $isActive;
    private int $failedLoginAttempts;
    private ?DateTimeImmutable $lockedUntil;
    private ?DateTimeImmutable $lastLoginAt;
    private array $profile;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    // Add these properties
    private string $firstName;
    private string $lastName;
    private ?DateTimeImmutable $emailVerifiedAt;
    private ?DateTimeImmutable $phoneVerifiedAt;

    public function __construct(
        UuidInterface $id,
        UuidInterface $tenantId,
        Email $email,
        string $passwordHash,
        string $passwordSalt,
        string $firstName,
        string $lastName,
        array $profile = []
    ) {
        $this->id = $id;
        $this->tenantId = $tenantId;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->passwordSalt = $passwordSalt;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = null;
        $this->address = null;
        $this->emailVerified = false;
        $this->phoneVerified = false;
        $this->twoFactorEnabled = false;
        $this->twoFactorSecret = null;
        $this->twoFactorRecoveryCodes = [];
        $this->isActive = true;
        $this->failedLoginAttempts = 0;
        $this->lockedUntil = null;
        $this->lastLoginAt = null;
        $this->emailVerifiedAt = null;
        $this->phoneVerifiedAt = null;
        $this->profile = $profile;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    // ========== GETTER METHODS ==========

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTenantId(): UuidInterface
    {
        return $this->tenantId;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getPasswordSalt(): string
    {
        return $this->passwordSalt;
    }

    public function getProfile(): array
    {
        return $this->profile;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ========== SETTER METHODS (For Repository) ==========

    public function setFailedLoginAttempts(int $attempts): void
    {
        $this->failedLoginAttempts = $attempts;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setLastLoginAt(?DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setEmailVerifiedAt(?DateTimeImmutable $emailVerifiedAt): void
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->emailVerified = ($emailVerifiedAt !== null);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setPhoneVerifiedAt(?DateTimeImmutable $phoneVerifiedAt): void
    {
        $this->phoneVerifiedAt = $phoneVerifiedAt;
        $this->phoneVerified = ($phoneVerifiedAt !== null);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setTwoFactorProperties(?string $secret, array $recoveryCodes): void
    {
        $this->twoFactorSecret = $secret;
        $this->twoFactorRecoveryCodes = $recoveryCodes;
        $this->twoFactorEnabled = ($secret !== null);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setLockedUntil(?DateTimeImmutable $lockedUntil): void
    {
        $this->lockedUntil = $lockedUntil;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setProfile(array $profile): void
    {
        $this->profile = $profile;
        $this->updatedAt = new DateTimeImmutable();
    }

    // ========== BUSINESS LOGIC METHODS ==========

    public function verifyEmail(): void
    {
        $this->emailVerified = true;
        $this->emailVerifiedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setPhone(PhoneNumber $phone): void
    {
        $this->phone = $phone;
        $this->phoneVerified = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function verifyPhone(): void
    {
        if (!$this->phone) {
            throw new \DomainException('No phone number set to verify');
        }

        $this->phoneVerified = true;
        $this->phoneVerifiedAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isPhoneVerified(): bool
    {
        return $this->phoneVerified && $this->phone !== null;
    }

    public function getPhoneVerifiedAt(): ?DateTimeImmutable
    {
        return $this->phoneVerifiedAt;
    }

    public function setAddress(Address $address): void
    {
        $this->address = $address;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function enableTwoFactor(string $secret, array $recoveryCodes): void
    {
        $this->twoFactorEnabled = true;
        $this->twoFactorSecret = $secret;
        $this->twoFactorRecoveryCodes = $recoveryCodes;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function disableTwoFactor(): void
    {
        $this->twoFactorEnabled = false;
        $this->twoFactorSecret = null;
        $this->twoFactorRecoveryCodes = [];
        $this->updatedAt = new DateTimeImmutable();
    }

    public function hasTwoFactorEnabled(): bool
    {
        return $this->twoFactorEnabled;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }

    public function getTwoFactorRecoveryCodes(): array
    {
        return $this->twoFactorRecoveryCodes;
    }

    public function isLocked(): bool
    {
        if ($this->lockedUntil === null) {
            return false;
        }

        return $this->lockedUntil > new DateTimeImmutable();
    }

    public function lock(int $minutes = 30): void
    {
        $this->lockedUntil = (new DateTimeImmutable())->modify("+{$minutes} minutes");
        $this->updatedAt = new DateTimeImmutable();
    }

    public function unlock(): void
    {
        $this->lockedUntil = null;
        $this->failedLoginAttempts = 0;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getLockedUntil(): ?DateTimeImmutable
    {
        return $this->lockedUntil;
    }

    public function recordSuccessfulLogin(): void
    {
        $this->lastLoginAt = new DateTimeImmutable();
        $this->failedLoginAttempts = 0;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function recordFailedLogin(): void
    {
        $this->failedLoginAttempts++;

        if ($this->failedLoginAttempts >= 5) {
            $this->lock();
        }

        $this->updatedAt = new DateTimeImmutable();
    }

    public function getFailedLoginAttempts(): int
    {
        return $this->failedLoginAttempts;
    }

    public function getLastLoginAt(): ?DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function isActive(): bool
    {
        return $this->isActive && !$this->isLocked();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateProfile(array $profile): void
    {
        $this->profile = array_merge($this->profile, $profile);
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updateName(string $firstName, string $lastName): void
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function updatePassword(string $passwordHash, string $passwordSalt): void
    {
        $this->passwordHash = $passwordHash;
        $this->passwordSalt = $passwordSalt;
        $this->updatedAt = new DateTimeImmutable();
    }
}
