<?php
$pageTitle = $pageTitle ?? 'Consultório Médico';
?>
<!doctype html>
<html lang="pt-BR">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> - Consultório Médico</title>
  <link rel="stylesheet" href="assets/style.css">
</head>

<body>
  <header class="topbar">
    <div class="brand">Consultório Médico</div>
    <?php if (!empty($_SESSION['user_id'])): ?>
      <div class="user-area">Olá, <?= e(current_user_name()) ?> <a class="logout" href="logout.php">Sair</a></div>
    <?php endif; ?>
  </header>
  <main class="container">
    <?php show_flash(); ?>