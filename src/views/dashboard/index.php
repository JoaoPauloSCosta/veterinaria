<?php require_login(); ?>
<h3 class="mb-3 d-flex justify-content-between align-items-center">
  <span>Dashboard</span>
  <?php start_session_safe(); if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
    <a href="<?= e(APP_URL) ?>/users/new" class="btn btn-primary btn-top"><i class="fa-solid fa-user-plus"></i> Cadastrar Usuário</a>
  <?php endif; ?>
</h3>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="text-muted">Próximos atendimentos</h6>
        <div id="upcoming-appointments">Carregando...</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="text-muted">Caixa do dia</h6>
        <div id="daily-cash">R$ 0,00</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="text-muted">Estoque crítico</h6>
        <div id="low-stock">Carregando...</div>
      </div>
    </div>
  </div>
</div>
