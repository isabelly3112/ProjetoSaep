<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        flash('error', 'Faça login para acessar o sistema.');
        redirect('login.php');
    }
    $last = (int)($_SESSION['last_activity'] ?? 0);
    if ($last > 0 && time() - $last > SESSION_TIMEOUT) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('error', 'Sua sessão expirou. Entre novamente.');
        redirect('login.php');
    }
    $_SESSION['last_activity'] = time();
}

function current_user_name(): string {
    return (string)($_SESSION['user_name'] ?? 'Usuário');
}
