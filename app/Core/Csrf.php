<?php
namespace App\Core;

class Csrf
{
    // lifetime in seconds (optional)
    const TOKEN_LIFETIME = 60 * 60; // 1 hour

    // ensure session is started before calling any method
    public static function ensureSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    // Generate (or return) current token
    public static function getToken(): string
    {
        self::ensureSession();
        // store token and created time
        if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_created_at']) ||
            (time() - intval($_SESSION['csrf_token_created_at']) > self::TOKEN_LIFETIME)
        ) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_created_at'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    // Echo hidden input (call inside form)
    public static function inputField(): void
    {
        $token = self::getToken();
        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    // Verify token (returns bool)
    public static function verifyToken(?string $token): bool
    {
        self::ensureSession();
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        // timing-safe compare
        $valid = hash_equals($_SESSION['csrf_token'], $token);
        // Optional: rotate token on successful verification (prevents double-post reuse)
        if ($valid) {
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_created_at']);
        }
        return $valid;
    }

    // Helper: require valid token or abort
    public static function requireValidToken(?string $token)
    {
        if (!self::verifyToken($token)) {
            http_response_code(403);
            echo "CSRF verification failed.";
            exit;
        }
    }
}
