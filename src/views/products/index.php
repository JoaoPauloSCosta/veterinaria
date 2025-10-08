<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Estoque & Produtos</h3>
  <div class="d-flex gap-2">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">Novo Produto</button>
    <button class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#stockReportModal">
      <i class="fa-solid fa-chart-line me-1"></i> Relatório de Movimentações
    </button>
  </div>
</div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger"><?= e($flash_error) ?></div>
<?php endif; ?>
<div class="row g-3">
  <div class="col-md-8">
    <form class="row g-2 mb-3" method="get" action="<?= e(APP_URL) ?>/products">
      <div class="col-auto"><input class="form-control" name="q" placeholder="Buscar" value="<?= e($q ?? '') ?>"></div>
      <div class="col-auto"><button class="btn btn-outline-secondary">Buscar</button></div>
    </form>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead><tr><th>ID</th><th>Nome</th><th>Preço</th><th>Estoque</th><th>Serviço</th><th class="text-end">Ações</th></tr></thead>
        <tbody>
          <?php foreach (($products ?? []) as $p): ?>
            <tr>
              <td><?= e($p['id']) ?></td>
              <td><?= e($p['name']) ?></td>
              <td>R$ <?= number_format((float)$p['price'], 2, ',', '.') ?></td>
              <td><?= e($p['stock_quantity']) ?></td>
              <td><?= $p['is_service'] ? 'Sim' : 'Não' ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#productModalEdit<?= e($p['id']) ?>"><i class="fa-regular fa-pen-to-square"></i> Editar</button>
                <form action="<?= e(APP_URL) ?>/products/<?= e($p['id']) ?>/delete" method="post" class="d-inline" onsubmit="return confirm('Excluir produto?');">
                  <?= csrf_input() ?>
                  <button class="btn btn-sm btn-danger"><i class="fa-regular fa-trash-can"></i> Excluir</button>
                </form>
              </td>
            </tr>
            <div class="modal fade" id="productModalEdit<?= e($p['id']) ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header"><h5 class="modal-title">Editar Produto</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button></div>
                  <div class="modal-body">
                    <form method="post" action="<?= e(APP_URL) ?>/products/<?= e($p['id']) ?>/edit">
                      <?= csrf_input() ?>
                      <div class="mb-2"><label class="form-label">Nome</label><input name="name" class="form-control" required value="<?= e($p['name']) ?>"></div>
                      <div class="mb-2"><label class="form-label">Descrição</label><textarea name="description" class="form-control"><?= e($p['description']) ?></textarea></div>
                      <div class="mb-2"><label class="form-label">Preço</label><input type="number" step="0.01" name="price" class="form-control" value="<?= e($p['price']) ?>" required></div>
                      <div class="mb-2"><label class="form-label">Estoque mínimo</label><input type="number" name="min_stock_level" class="form-control" value="<?= e($p['min_stock_level']) ?>"></div>
                      <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="is_service<?= e($p['id']) ?>" name="is_service" <?= $p['is_service'] ? 'checked' : '' ?>><label class="form-check-label" for="is_service<?= e($p['id']) ?>">É serviço</label></div>
                      <div class="text-end"><button class="btn btn-primary">Salvar</button></div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navegação de páginas" class="mt-3">
      <ul class="pagination justify-content-center">
        <!-- Botão Anterior -->
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
          </li>
        <?php else: ?>
          <li class="page-item disabled">
            <span class="page-link">Anterior</span>
          </li>
        <?php endif; ?>

        <!-- Páginas -->
        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        if ($startPage > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
          </li>
          <?php if ($startPage > 2): ?>
            <li class="page-item disabled">
              <span class="page-link">...</span>
            </li>
          <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
          <?php if ($endPage < $totalPages - 1): ?>
            <li class="page-item disabled">
              <span class="page-link">...</span>
            </li>
          <?php endif; ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>"><?= $totalPages ?></a>
          </li>
        <?php endif; ?>

        <!-- Botão Próximo -->
        <?php if ($page < $totalPages): ?>
          <li class="page-item">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próximo</a>
          </li>
        <?php else: ?>
          <li class="page-item disabled">
            <span class="page-link">Próximo</span>
          </li>
        <?php endif; ?>
      </ul>
    </nav>

    <!-- Informações da paginação -->
    <div class="text-center text-muted small mt-2">
      Mostrando <?= min($limit, $total - (($page - 1) * $limit)) ?> de <?= $total ?> registros
      (Página <?= $page ?> de <?= $totalPages ?>)
    </div>
    <?php endif; ?>
  </div>
  <div class="col-md-4">
    <div class="card mb-3" id="restock"><div class="card-body">
      <h6>Entrada de Estoque</h6>
      <form method="post" action="<?= e(APP_URL) ?>/stock/entry">
        <?= csrf_input() ?>
        <div class="mb-2"><label class="form-label">Produto</label>
          <select class="form-select" name="product_id" required>
            <option value="">Selecione...</option>
            <?php foreach (($products ?? []) as $prod): ?>
              <option value="<?= e($prod['id']) ?>"><?= e($prod['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Quantidade</label><input class="form-control" name="quantity" type="number" min="1" required></div>
        <?php $catalog = stock_reason_catalog(); $entradaReasons = $catalog['entrada'] ?? []; ?>
        <div class="mb-2"><label class="form-label">Motivo</label>
          <select class="form-select" name="reason_code" required>
            <option value="">Selecione o motivo...</option>
            <?php foreach ($entradaReasons as $code => $desc): ?>
              <option value="<?= e($code) ?>"><?= e($desc) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Obrigatório. O sistema não permite movimentar sem motivo.</small>
        </div>
        <div class="mb-2"><label class="form-label">Lote (opcional)</label><input class="form-control" name="batch" placeholder="Ex.: LOTE-2025-01"></div>
        <div class="mb-2"><label class="form-label">Observação (opcional)</label><textarea class="form-control" name="notes" rows="2" placeholder="Inclua detalhes, fornecedor, NF, ajuste, etc."></textarea></div>
        <div class="text-end"><button class="btn btn-success">Registrar</button></div>
      </form>
    </div></div>
    <div class="card"><div class="card-body">
      <h6>Saída de Estoque</h6>
      <form method="post" action="<?= e(APP_URL) ?>/stock/exit">
        <?= csrf_input() ?>
        <div class="mb-2"><label class="form-label">Produto</label>
          <select class="form-select" name="product_id" required>
            <option value="">Selecione...</option>
            <?php foreach (($products ?? []) as $prod): ?>
              <option value="<?= e($prod['id']) ?>"><?= e($prod['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label">Quantidade</label><input class="form-control" name="quantity" type="number" min="1" required></div>
        <?php $saidaReasons = $catalog['saida'] ?? []; ?>
        <div class="mb-2"><label class="form-label">Motivo</label>
          <select class="form-select" name="reason_code" required>
            <option value="">Selecione o motivo...</option>
            <?php foreach ($saidaReasons as $code => $desc): ?>
              <option value="<?= e($code) ?>"><?= e($desc) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Obrigatório. O sistema não permite movimentar sem motivo.</small>
        </div>
        <div class="mb-2"><label class="form-label">Lote (opcional)</label><input class="form-control" name="batch" placeholder="Ex.: LOTE-2025-01"></div>
        <div class="mb-2"><label class="form-label">Observação (opcional)</label><textarea class="form-control" name="notes" rows="2" placeholder="Inclua detalhes da saída (venda, serviço, ajuste, etc.)"></textarea></div>
        <div class="text-end"><button class="btn btn-warning">Registrar</button></div>
      </form>
    </div></div>

    <div class="card mt-3"><div class="card-body">
      <h6>Estoque crítico</h6>
      <ul class="list-group list-group-flush">
        <?php foreach (($low ?? []) as $it): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><?= e($it['name']) ?></span>
            <span>
              <span class="badge bg-danger me-2"><?= e($it['stock_quantity']) ?></span>
              <a class="btn btn-sm btn-outline-primary" href="<?= e(APP_URL) ?>/products?restock_id=<?= e((int)$it['id']) ?>#restock">Repor</a>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div></div>
  </div>
</div>

<!-- Modal Relatório de Movimentações -->
<div class="modal fade" id="stockReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <div>
          <h5 class="modal-title">Relatório de Movimentações de Estoque</h5>
          <div class="text-muted small">Filtre por tipo, motivo, usuário e período</div>
        </div>
        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body pt-0">
        <form class="row g-3 align-items-end mb-3" method="get" action="<?= e(APP_URL) ?>/products">
          <div class="col-md-2">
            <label class="form-label">Tipo</label>
            <select name="mtype" class="form-select">
              <option value="">Todos</option>
              <option value="entrada" <?= ($mtype ?? '')==='entrada' ? 'selected' : '' ?>>Entrada</option>
              <option value="saida" <?= ($mtype ?? '')==='saida' ? 'selected' : '' ?>>Saída</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Motivo</label>
            <?php $cat = stock_reason_catalog(); $allReasons = array_merge($cat['entrada'] ?? [], $cat['saida'] ?? []); ?>
            <select name="reason_code" class="form-select">
              <option value="">Todos</option>
              <?php foreach ($allReasons as $code => $desc): ?>
                <option value="<?= e($code) ?>" <?= ($mreason ?? '')===$code ? 'selected' : '' ?>><?= e($desc) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Usuário</label>
            <input class="form-control" name="user_name" type="text" value="<?= e($muserName ?? '') ?>" placeholder="Nome do usuário">
          </div>
          <div class="col-md-2">
            <label class="form-label">De</label>
            <input class="form-control" name="from" type="date" value="<?= e($mfrom ?? '') ?>">
          </div>
          <div class="col-md-2">
            <label class="form-label">Até</label>
            <input class="form-control" name="to" type="date" value="<?= e($mto ?? '') ?>">
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-outline-secondary">
              <i class="fa-solid fa-filter me-1"></i> Filtrar
            </button>
          </div>
        </form>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Data</th><th>Tipo</th><th>Produto</th><th>Qtd</th><th>Motivo</th><th>Usuário</th><th>Lote</th><th>Obs.</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (($movements ?? []) as $mv): ?>
                <tr>
                  <td><?= e(date('d/m/Y H:i', strtotime($mv['created_at'] ?? ''))) ?></td>
                  <td><span class="badge bg-<?= ($mv['type'] ?? '')==='entrada' ? 'success' : 'warning' ?>"><?= e(ucfirst($mv['type'])) ?></span></td>
                  <td><?= e($mv['product_name'] ?? $mv['product_id']) ?></td>
                  <td><?= e($mv['quantity']) ?></td>
                  <td><?= e($mv['reason']) ?></td>
                  <td><?= e($mv['user_name'] ?? $mv['user_id']) ?></td>
                  <td><?= e($mv['batch'] ?? '') ?></td>
                  <td class="small text-muted"><?= e($mv['notes'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($movements ?? [])): ?>
                <tr><td colspan="8" class="text-center text-muted">Sem movimentações para os filtros informados.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
  // Abrir automaticamente o relatório se já houver filtros na URL
  document.addEventListener('DOMContentLoaded', function(){
    var params = new URLSearchParams(window.location.search);
    var hasFilters = params.has('mtype') || params.has('reason_code') || params.has('user_name') || params.has('from') || params.has('to');
    var repEl = document.getElementById('stockReportModal');
    if (!repEl) return;
    var modal = new bootstrap.Modal(repEl);

    // Persistir estado de fechamento para evitar reabrir quando usuário fechar manualmente
    var closedFlag = sessionStorage.getItem('stockReportClosed') === 'true';
    if (hasFilters && !closedFlag) { modal.show(); }

    repEl.addEventListener('hidden.bs.modal', function(){ sessionStorage.setItem('stockReportClosed', 'true'); });
    repEl.addEventListener('show.bs.modal', function(){ sessionStorage.setItem('stockReportClosed', 'false'); });

    // Ao aplicar filtros, garantir que o modal reabra após o reload
    var filterForm = repEl.querySelector('form');
    if (filterForm) {
      filterForm.addEventListener('submit', function(){ sessionStorage.setItem('stockReportClosed', 'false'); });
    }
  });

  // Auto-selecionar produto para reposição via ?restock_id=ID
  document.addEventListener('DOMContentLoaded', function(){
    var params = new URLSearchParams(window.location.search);
    var rid = params.get('restock_id');
    if (rid) {
      var restockCard = document.getElementById('restock');
      var sel = restockCard ? restockCard.querySelector('select[name="product_id"]') : null;
      if (sel) {
        sel.value = rid;
        restockCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    }
  });
</script>

<!-- Modal Novo Produto -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Novo Produto</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/products/create">
          <?= csrf_input() ?>
          <div class="mb-2"><label class="form-label">Nome</label><input name="name" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Descrição</label><textarea name="description" class="form-control"></textarea></div>
          <div class="mb-2"><label class="form-label">Preço</label><input type="number" step="0.01" name="price" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Estoque inicial</label><input type="number" name="stock_quantity" class="form-control" value="0"></div>
          <div class="mb-2"><label class="form-label">Estoque mínimo</label><input type="number" name="min_stock_level" class="form-control" value="0"></div>
          <div class="form-check mb-2"><input class="form-check-input" type="checkbox" id="is_service_new" name="is_service"><label class="form-check-label" for="is_service_new">É serviço</label></div>
          <div class="text-end"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
