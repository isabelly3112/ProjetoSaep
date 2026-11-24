<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
$pageTitle = 'Tela Inicial';
require __DIR__ . '/includes/header.php';
?>
<section class="panel">
    <h1>Bem-vindo(a), <?= e(current_user_name()) ?>!</h1>
    <p>Escolha uma das opções para continuar:</p>
    <div class="grid">
        <a class="card" href="acompanhantes.php">Gestão de Acompanhantes</a>
        <a class="card" href="pacientes.php">Gestão de Pacientes</a>
        <a class="card" href="agendamentos.php">Gestão de Agendamentos</a>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>