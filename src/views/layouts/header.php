<?php
start_session_safe();
$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Sistema Veterinário') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/style.css">
</head>
<body>

<?php
// Obter contagem de notificações não lidas
$unreadCount = 0;
if (isset($_SESSION['user'])) {
    require_once __DIR__ . '/../../models/NotificationModel.php';
    $unreadCount = NotificationModel::countUnreadByUserId($_SESSION['user']['id']);
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?= e(APP_URL) ?>/dashboard"><i class="fa-solid fa-paw"></i> VetSystem</a>
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
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/veterinarians">Veterinários</a></li>
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
      <!-- Área direita: notificações, nome do usuário e sair -->
      <ul class="navbar-nav ms-lg-auto align-items-lg-center">
        <?php if ($user): ?>
          <!-- Sino de Notificações -->
          <li class="nav-item dropdown me-3">
            <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-regular fa-bell fs-5"></i>
              <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger <?= $unreadCount > 0 ? '' : 'd-none' ?>" style="font-size: 0.6rem;">
                <?= $unreadCount ?>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
              <li class="dropdown-header d-flex justify-content-between align-items-center">
                <span>Notificações</span>
                <button id="mark-all-read" class="btn btn-sm btn-outline-primary" style="font-size: 0.7rem;">Marcar todas como lidas</button>
              </li>
              <li><hr class="dropdown-divider"></li>
              <div id="notifications-list">
                <li class="dropdown-item text-center text-muted">Carregando...</li>
              </div>
            </ul>
          </li>
          <li class="nav-item d-none d-lg-block"><span class="navbar-text me-3"><i class="fa-regular fa-user"></i> <?= e($user['name']) ?></span></li>
          <li class="nav-item"><a class="nav-link" href="<?= e(APP_URL) ?>/logout"><i class="fa-solid fa-right-from-bracket"></i> Sair</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-4">
