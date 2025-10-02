<?php
start_session_safe();
$user = $_SESSION['user'] ?? null;
?>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(APP_URL) ?>/dashboard">
      <?= e(APP_NAME) ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <?php if ($user): ?>
        <div class="w-100 d-lg-none text-center text-light opacity-75 small py-1">Olá, <?= e($user['name']) ?></div>
      <?php endif; ?>
      <!-- Menu centralizado (desktop) -->
      <ul class="navbar-nav mx-lg-auto justify-content-center">
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/dashboard">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/clients">Clientes</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/pets">Pets</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/agenda">Agenda</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/products">Estoque</a></li>
          <?php if (($user['role'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/sales/pos">PDV</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="#">Relatórios</a></li>
          <?php if (($user['role'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/settings">Configurações</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <!-- Área direita: nome do usuário e sair -->
      <ul class="navbar-nav ms-lg-auto align-items-lg-center">
        <?php if ($user): ?>
          <li class="nav-item d-none d-lg-block"><span class="navbar-text me-3"><i class="fa-regular fa-user"></i> <?= e($user['name']) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4">
