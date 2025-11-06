<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Csrf;
use PDO;
use PDOException;

class AuthController
{
    // Show register form (optionally with $error)
    public function showRegister($error = null)
    {
        include __DIR__ . '/../Views/auth/register.php';
    }

    // Handle registration POST
    public function register()
    {
        // verify CSRF
        Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        // simple trimming + validation
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Please fill all required fields.';
            include __DIR__ . '/../Views/auth/register.php';
            return;
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed
            ]);

            // registration successful — redirect to login
            header('Location: /login');
            exit;
        } catch (PDOException $e) {
            // detect duplicate email simple check (MySQL error code 1062)
            if ($e->getCode() == 23000) {
                $error = 'Email already registered.';
            } else {
                // for dev you can show $e->getMessage() but avoid in production
                $error = 'Registration failed. Please try again.';
            }
            include __DIR__ . '/../Views/auth/register.php';
            return;
        }
    }

    // Show login form (optionally with $error)
    public function showLogin($error = null)
    {
        include __DIR__ . '/../Views/auth/login.php';
    }

    // Handle login POST
    public function login()
    {
        // verify CSRF
        Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        session_start();

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            $error = 'Please provide email and password.';
            include __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // set a minimal session user payload
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ];

            // regenerate session id to prevent fixation
            session_regenerate_id(true);

            $redirect = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid credentials.';
            include __DIR__ . '/../Views/auth/login.php';
            return;
        }
    }

    public function logout()
    {
        session_start();
        // Unset and destroy session
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        header('Location: /login');
        exit;
    }
}
