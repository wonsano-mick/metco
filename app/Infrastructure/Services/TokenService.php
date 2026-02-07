<?php

namespace App\Infrastructure\Services;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Log;
use App\Models\Eloquent\UserSession;
use Illuminate\Support\Facades\Hash;

class TokenService
{
    private string $jwtSecret;
    private int $accessTokenTtl;
    private int $refreshTokenTtl;

    public function __construct()
    {
        // Ensure we have a proper JWT secret
        $this->jwtSecret = config('app.key');

        // Fallback if app.key is too short
        if (strlen($this->jwtSecret) < 32) {
            $this->jwtSecret = hash('sha256', $this->jwtSecret);
        }

        $this->accessTokenTtl = config('jwt.ttl', 3600); // 1 hour
        $this->refreshTokenTtl = config('jwt.refresh_ttl', 86400 * 30); // 30 days
    }

    public function generateTokens(
        string $userId,
        string $deviceId,
        array $deviceInfo,
        string $ipAddress,
        string $userAgent
    ): array {
        $accessToken = $this->generateAccessToken($userId, $deviceId);
        $refreshToken = bin2hex(random_bytes(32));

        // Store session
        UserSession::create([
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $userId,
            'device_id' => $deviceId,
            'device_info' => json_encode($deviceInfo),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'access_token_hash' => Hash::make($accessToken),
            'refresh_token_hash' => Hash::make($refreshToken),
            'expires_at' => Carbon::now()->addSeconds($this->refreshTokenTtl),
            'last_activity_at' => Carbon::now(),
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => $this->accessTokenTtl,
            'token_type' => 'Bearer',
        ];
    }

    public function refreshToken(string $refreshToken, string $ipAddress, string $userAgent): array
    {
        // Find active session by refresh token
        $sessions = UserSession::where('expires_at', '>', Carbon::now())
            ->whereNull('revoked_at')
            ->get();

        foreach ($sessions as $session) {
            if (Hash::check($refreshToken, $session->refresh_token_hash)) {
                // Revoke old session
                $session->update(['revoked_at' => Carbon::now()]);

                // Generate new tokens
                return $this->generateTokens(
                    $session->user_id,
                    $session->device_id,
                    json_decode($session->device_info, true) ?? [],
                    $ipAddress,
                    $userAgent
                );
            }
        }

        throw new \DomainException('Invalid refresh token');
    }

    public function revokeTokens(string $userId, string $deviceId, string $ipAddress): void
    {
        UserSession::where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->update([
                'revoked_at' => Carbon::now(),
                'revoked_by_ip' => $ipAddress
            ]);
    }

    public function validateAccessToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Exception $e) {
            Log::error('Token validation failed', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 50) . '...'
            ]);
            return null;
        }
    }

    private function generateAccessToken(string $userId, string $deviceId): string
    {
        $payload = [
            'sub' => $userId,
            'device' => $deviceId,
            'iat' => time(),
            'exp' => time() + $this->accessTokenTtl,
            'jti' => Uuid::uuid4()->toString(),
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
}
