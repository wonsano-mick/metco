<?php

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\ValueObjects\Email;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Services\AuthenticationService;
use App\Infrastructure\Services\TokenService;
use App\Infrastructure\Services\TwoFactorService;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class AuthService
{
    private UserRepositoryInterface $userRepository;
    private AuthenticationService $authenticationService;
    private TokenService $tokenService;
    private TwoFactorService $twoFactorService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AuthenticationService $authenticationService,
        TokenService $tokenService,
        TwoFactorService $twoFactorService
    ) {
        $this->userRepository = $userRepository;
        $this->authenticationService = $authenticationService;
        $this->tokenService = $tokenService;
        $this->twoFactorService = $twoFactorService;
    }

    public function register(array $data, string $ipAddress, string $userAgent): array
    {
        // Check if user already exists
        $email = new Email($data['email']);
        $existingUser = $this->userRepository->findByEmail($email);

        if ($existingUser) {
            throw new \DomainException('User already exists with this email');
        }

        // Generate password hash and salt
        $passwordSalt = bin2hex(random_bytes(16));
        $passwordHash = Hash::make($data['password'] . $passwordSalt);

        // Create user entity
        $user = new User(
            Uuid::uuid4(),
            Uuid::uuid4(), // Default tenant - you might want to get this from request
            $email,
            $passwordHash,
            $passwordSalt,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            [
                'phone' => $data['phone'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'address' => $data['address'] ?? null,
            ]
        );

        // Save user
        $this->userRepository->save($user);

        // Send email verification (if enabled)
        // $this->sendVerificationEmail($user);

        return [
            'user_id' => $user->getId()->toString(),
            'requires_email_verification' => true,
            'requires_phone_verification' => !empty($data['phone']),
        ];
    }

    public function login(
        string $email,
        string $password,
        string $deviceId,
        array $deviceInfo,
        string $ipAddress,
        string $userAgent
    ): array {
        return $this->authenticationService->authenticate(
            $email,
            $password,
            $deviceId,
            $deviceInfo,
            $ipAddress,
            $userAgent
        );
    }

    public function verifyTwoFactor(
        string $userId,
        string $code,
        string $deviceId
    ): array {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if (!$user) {
            throw new \DomainException('User not found');
        }

        return $this->twoFactorService->verify(
            $user,
            $code,
            $deviceId
        );
    }

    public function logout(string $userId, string $deviceId, string $ipAddress): void
    {
        $this->tokenService->revokeTokens($userId, $deviceId, $ipAddress);
    }

    public function refreshToken(string $refreshToken, string $ipAddress, string $userAgent): array
    {
        return $this->tokenService->refreshToken($refreshToken, $ipAddress, $userAgent);
    }

    public function getProfile(string $userId): User
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if (!$user) {
            throw new \DomainException('User not found');
        }

        return $user;
    }

    public function enableTwoFactor(string $userId): array
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if (!$user) {
            throw new \DomainException('User not found');
        }

        return $this->twoFactorService->enable($user);
    }

    public function disableTwoFactor(string $userId, string $code): void
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if (!$user) {
            throw new \DomainException('User not found');
        }

        $this->twoFactorService->disable($user, $code);
    }

    public function changePassword(string $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->userRepository->findById(Uuid::fromString($userId));

        if (!$user) {
            throw new \DomainException('User not found');
        }

        // Verify current password
        $combinedPassword = $currentPassword . $user->getPasswordSalt();
        if (!Hash::check($combinedPassword, $user->getPasswordHash())) {
            throw new \DomainException('Current password is incorrect');
        }

        // Generate new password hash
        $newPasswordSalt = bin2hex(random_bytes(16));
        $newPasswordHash = Hash::make($newPassword . $newPasswordSalt);

        // Update user
        $user->updatePassword($newPasswordHash, $newPasswordSalt);
        $this->userRepository->save($user);
    }

    public function requestPasswordReset(string $email): void
    {
        $emailObj = new Email($email);
        $user = $this->userRepository->findByEmail($emailObj);

        if ($user) {
            // Generate reset token and send email
            // $this->sendPasswordResetEmail($user);
        }

        // Always return success (don't reveal if email exists)
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        // Validate token and update password
        // Implementation depends on your token storage
    }
}
