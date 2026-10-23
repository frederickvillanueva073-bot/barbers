
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

function requireLogin(): void
{
    if (
        empty($_SESSION['user_id']) ||
        empty($_SESSION['username']) ||
        empty($_SESSION['role'])
    ) {
        header('Location: index.php');
        exit();
    }
}

function requireAdmin(): void
{
    requireLogin();

    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('Access denied. Administrator only.');
    }
}
