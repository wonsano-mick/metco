<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    private Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function enable(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->enableTwoFactor($secret, $recoveryCodes);

        return [
            'secret' => $secret,
            'qr_code_url' => $this->google2fa->getQRCodeUrl(
                config('app.name'),
                $user->getEmail()->getValue(),
                $secret
            ),
            'recovery_codes' => $recoveryCodes,
        ];
    }

    public function disable(User $user, string $code): void
    {
        if (!$this->verify($user, $code, 'disable')) {
            throw new \DomainException('Invalid verification code');
        }

        $user->disableTwoFactor();
    }

    public function verify(User $user, string $code, string $deviceId = ''): array
    {
        if (!$user->hasTwoFactorEnabled()) {
            throw new \DomainException('Two-factor authentication is not enabled');
        }

        $secret = $user->getTwoFactorSecret();

        // Check if code is valid
        $valid = $this->google2fa->verifyKey($secret, $code);

        if (!$valid) {
            // Check if it's a recovery code
            $recoveryCodes = $user->getTwoFactorRecoveryCodes();
            $valid = in_array($code, $recoveryCodes);

            if ($valid) {
                // Remove used recovery code
                $newRecoveryCodes = array_diff($recoveryCodes, [$code]);
                $user->setTwoFactorProperties($secret, array_values($newRecoveryCodes));
            }
        }

        if (!$valid) {
            throw new \DomainException('Invalid verification code');
        }

        // Generate session for 2FA-verified device
        $sessionId = uniqid('2fa_', true);

        return [
            'verified' => true,
            'session_id' => $sessionId,
        ];
    }

    private function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(5)));
        }
        return $codes;
    }
}
