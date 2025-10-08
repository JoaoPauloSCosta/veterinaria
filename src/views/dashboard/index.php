<?php require_login(); ?>
<?php require_once __DIR__ . '/../../models/ProductModel.php'; ?>
<?php $low = ProductModel::lowStock(); ?>

<h3 class="mb-3 d-flex justify-content-between align-items-center">
  <span>Dashboard</span>
  <?php start_session_safe(); if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
    <a href="<?= e(APP_URL) ?>/users/new" class="btn btn-primary btn-top"><i class="fa-solid fa-user-plus"></i> Cadastrar Usuário</a>
  <?php endif; ?>
</h3>

<?php if (!empty($low)): ?>
  <div class="alert alert-danger d-flex justify-content-between align-items-center">
    <div>
      <i class="fa-solid fa-triangle-exclamation me-1"></i>
      Estoque crítico: <?= count($low) ?> produto(s) abaixo do mínimo.
    </div>
    <a href="<?= e(APP_URL) ?>/products" class="btn btn-danger btn-sm text-white">Ver estoque</a>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <h6 class="text-muted">Próximos atendimentos</h6>
        <div id="upcoming-appointments" class="text-muted">Em breve</div>
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
        <?php if (!empty($low)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($low as $it): ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span><?= e($it['name']) ?></span>
                <span>
                  <span class="badge bg-danger me-2"><?= e((int)$it['stock_quantity']) ?></span>
                  <a class="btn btn-sm btn-outline-primary" href="<?= e(APP_URL) ?>/products?restock_id=<?= e((int)$it['id']) ?>#restock">Repor</a>
                </span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-muted">Sem itens críticos</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
