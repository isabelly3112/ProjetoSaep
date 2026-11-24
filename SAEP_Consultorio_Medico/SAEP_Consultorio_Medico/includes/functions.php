<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('A solicitação expirou. Volte e tente novamente.');
    }
}

function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo '<div class="alert ' . e($f['type']) . '">' . e($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function cpf_digits(string $cpf): string {
    return preg_replace('/\D+/', '', $cpf) ?? '';
}

function valid_cpf_format(string $cpf): bool {
    return strlen(cpf_digits($cpf)) === 11;
}

function encrypt_cpf(string $cpf): string {
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt(cpf_digits($cpf), 'aes-256-gcm', CPF_ENCRYPTION_KEY,
        OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) {
        throw new RuntimeException('Não foi possível proteger o CPF.');
    }
    return base64_encode($iv . $tag . $cipher);
}

/* Os CPFs cadastrados são sempre exibidos mascarados. */
function masked_cpf(?string $encrypted): string {
    return '***.***.***-**';
}

function valid_date(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
