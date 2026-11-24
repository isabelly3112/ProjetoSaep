<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $usuario = trim((string) ($_POST['usuario'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');
    if ($usuario === '' || $senha === '') {
        $error = 'Preencha usuário e senha.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nome, usuario, senha_hash FROM usuarios WHERE usuario = ? LIMIT 1');
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        if ($user && password_verify($senha, $user['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['last_activity'] = time();
            redirect('index.php');
        }
        $error = 'Usuário ou senha inválidos.';
    }
}
$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="panel login-box">
    <h1>Entrar no sistema</h1>
    <p class="muted">Informe seus dados para acessar o consultório.</p>
    <?php if ($error): ?>
        <div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <p><label for="usuario">Usuário</label><input id="usuario" name="usuario" required autocomplete="username"></p>
        <p><label for="senha">Senha</label><input id="senha" type="password" name="senha" required
                autocomplete="current-password"></p>
        <button type="submit">Entrar</button>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>