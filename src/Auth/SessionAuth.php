<?php

declare(strict_types=1);

namespace MsDashboard\Auth;

use MsDashboard\Config\Config;
use MsDashboard\Config\Database;
use PDO;

/**
 * Session authentication using cookies and MySQL session table.
 * Replaces the duplicated cookie-check logic from all partner pages.
 */
final readonly class SessionAuth
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connect();
    }

    /**
     * Check if the current request has a valid, non-expired session.
     * Also accepts APP_TOKEN via ?token= query param (for mobile app bypass).
     */
    public function isAuthenticated(): bool
    {
        // Token-based bypass (Android WebView)
        if ($this->isTokenAuthenticated()) {
            return true;
        }

        $sessionToken = $_COOKIE['sessionreport'] ?? null;

        if ($sessionToken === null || $sessionToken === '') {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'SELECT Expired FROM `session` WHERE Session = :session LIMIT 1'
        );
        $stmt->execute([':session' => $sessionToken]);
        $row = $stmt->fetch();

        if (!$row) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        return $now <= $row['Expired'];
    }

    /**
     * Check if the request has a valid APP_TOKEN (no expiration).
     */
    public function isTokenAuthenticated(): bool
    {
        $token = $_GET['token'] ?? $_SERVER['HTTP_X_APP_TOKEN'] ?? null;

        if ($token === null || $token === '') {
            return false;
        }

        $config   = Config::load();
        $appToken = $config->get('APP_TOKEN', '');

        return $appToken !== '' && hash_equals($appToken, $token);
    }

    /**
     * Require authentication — redirect to login if not authenticated.
     */
    public function requireAuth(string $loginUrl = '/login'): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Process login attempt. Returns session token on success, null on failure.
     */
    public function login(string $username, string $hashedPassword): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM user WHERE UserName = :user AND Password = :pass'
        );
        $stmt->execute([':user' => $username, ':pass' => $hashedPassword]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        // Delete existing sessions for this user
        $delete = $this->pdo->prepare('DELETE FROM `session` WHERE UserName = :user');
        $delete->execute([':user' => $username]);

        // Create new session
        $token = bin2hex(random_bytes(6));
        $insert = $this->pdo->prepare(
            'INSERT INTO `session` (UserName, Session, Expired) VALUES (:user, :session, DATE_ADD(NOW(), INTERVAL 8 HOUR))'
        );
        $insert->execute([':user' => $username, ':session' => $token]);

        return $token;
    }

    /**
     * Process login and return JSON response array.
     */
    public function handleLoginRequest(string $username, string $hashedPassword): array
    {
        $token = $this->login($username, $hashedPassword);

        if ($token !== null) {
            return ['status' => 'Success', 'msg' => $token];
        }

        return ['status' => 'Error', 'msg' => 'Wrong User or Password :('];
    }
}
