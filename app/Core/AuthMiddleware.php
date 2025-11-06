<?php
namespace App\Core;

class AuthMiddleware
{
    // Ensure session is started
    public static function ensureSession()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    // Call this at the top of protected routes/controllers
    // If not logged in, save intended URL and redirect to /login
    public static function requireAuth()
    {
        self::ensureSession();

        if (empty($_SESSION['user']) || empty($_SESSION['user']['id'])) {
            // Save intended URL so we can redirect back after login
            $intended = $_SERVER['REQUEST_URI'] ?? '/';
            $_SESSION['intended_url'] = $intended;

            header('Location: /login');
            exit;
        }
        // otherwise: user is present in session — allow execution to continue
    }

    // Optional helper to get current user id (or null)
    public static function userId()
    {
        self::ensureSession();
        return $_SESSION['user']['id'] ?? null;
    }
}
