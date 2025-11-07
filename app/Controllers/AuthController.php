<?php
namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Flash;
use App\Core\View;
use PDO;

class AuthController
{
    /* ---------- Views ---------- */

    public function showRegister(array $old = [], array $errors = [])
    {
        View::render('auth/register', [
            'title'  => 'Register',
            'old'    => $old,
            'errors' => $errors,
        ]);
    }

    public function showLogin(array $old = [], array $errors = [])
    {
        View::render('auth/login', [
            'title'  => 'Login',
            'old'    => $old,
            'errors' => $errors,
        ]);
    }

    /* ---------- Actions ---------- */

    public function register()
    {
        Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $name     = trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if ($name === '')   $errors['name'] = 'Name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
        if (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters.';
        if ($password !== $confirm) $errors['password_confirmation'] = 'Passwords do not match.';

        if ($errors) {
            $this->showRegister($_POST, $errors);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Unique email check
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->showRegister($_POST, ['email' => 'Email is already registered.']);
            return;
        }

        // Create user
        $stmt = $db->prepare("
          INSERT INTO users (name, email, password, created_at, updated_at)
          VALUES (:name, :email, :password, NOW(), NOW())
        ");
        $stmt->execute([':name'=>$name, ':email'=>$email, ':password'=>$password]);
        $uid = (int)$db->lastInsertId();

        // Log in the new user
        $this->loginUser(['id'=>$uid, 'name'=>$name, 'email'=>$email]);

        Flash::success('Welcome! Your account has been created.');
        header('Location: /dashboard'); exit;
    }

    public function login()
    {
        Csrf::requireValidToken($_POST['csrf_token'] ?? null);

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email required.';
        if ($password === '') $errors['password'] = 'Password is required.';

        if ($errors) {
            $this->showLogin($_POST, $errors);
            return;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->showLogin(['email'=>$email], ['auth' => 'Invalid email or password.']);
            return;
        }

        $this->loginUser($user);

        Flash::success('Logged in successfully.');
        header('Location: /dashboard'); exit;
    }

    public function logout()
    {
        // Clear session safely
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        // Start a fresh session to carry the flash
        session_start();
        Flash::info('You have been logged out.');
        header('Location: /login'); exit;
    }

    /* ---------- Helpers ---------- */

    private function loginUser(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        session_regenerate_id(true); // prevent fixation
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
        ];
    }
}
