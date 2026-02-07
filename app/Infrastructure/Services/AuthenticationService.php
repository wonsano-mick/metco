<?php

namespace App\Infrastructure\Services;

use App\Domain\ValueObjects\Email;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use App\Domain\Repositories\UserRepositoryInterface;

class AuthenticationService
{
    private UserRepositoryInterface $userRepository;
    private TokenService $tokenService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenService $tokenService
    ) {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
    }

    public function authenticate(
        string $email,
        string $password,
        string $deviceId,
        array $deviceInfo,
        string $ipAddress,
        string $userAgent
    ): array {
        // Rate limiting check
        $throttleKey = 'auth:' . $ipAddress . '|' . $email;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw new \DomainException('Too many login attempts. Please try again later.', 429);
        }

        try {
            // Find user by email
            $emailObj = new Email($email);
            $user = $this->userRepository->findByEmail($emailObj);

            if (!$user) {
                RateLimiter::hit($throttleKey);
                throw new \DomainException('Invalid credentials');
            }

            // Verify password
            $combinedPassword = $password . $user->getPasswordSalt();
            if (!Hash::check($combinedPassword, $user->getPasswordHash())) {
                RateLimiter::hit($throttleKey);

                // Record failed login attempt
                $user->recordFailedLogin();
                $this->userRepository->save($user);

                throw new \DomainException('Invalid credentials');
            }

            // Check if account is active
            if (!$user->isActive()) {
                throw new \DomainException('Account is disabled. Please contact support.');
            }

            // Check if account is locked
            if ($user->isLocked()) {
                $lockedUntil = $user->getLockedUntil();
                if ($lockedUntil) {
                    $minutes = ceil(($lockedUntil->getTimestamp() - time()) / 60);
                    throw new \DomainException("Account is locked. Try again in {$minutes} minutes.");
                }
                throw new \DomainException('Account is locked. Please try again later.');
            }

            // Clear rate limiter on successful login
            RateLimiter::clear($throttleKey);

            // Record successful login (UPDATED THIS LINE)
            $user->recordSuccessfulLogin(); // Use this instead of updateLastLogin()

            // Save user
            $this->userRepository->save($user);

            // Generate tokens
            $tokens = $this->tokenService->generateTokens(
                $user->getId()->toString(),
                $deviceId,
                $deviceInfo,
                $ipAddress,
                $userAgent
            );

            return [
                'user' => [
                    'id' => $user->getId()->toString(),
                    'email' => $user->getEmail()->getValue(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'email_verified' => $user->isEmailVerified(),
                    'phone_verified' => $user->isPhoneVerified(),
                    'two_factor_enabled' => $user->hasTwoFactorEnabled(),
                    'last_login_at' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
                ],
                'tokens' => $tokens,
                'requires_2fa' => $user->hasTwoFactorEnabled(),
            ];
        } catch (\DomainException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Authentication failed', [
                'email' => $email,
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \DomainException('Authentication failed. Please try again.');
        }
    }

    public function validateUserCredentials(string $email, string $password): bool
    {
        $emailObj = new Email($email);
        $user = $this->userRepository->findByEmail($emailObj);

        if (!$user) {
            return false;
        }

        $combinedPassword = $password . $user->getPasswordSalt();
        return Hash::check($combinedPassword, $user->getPasswordHash());
    }
}
