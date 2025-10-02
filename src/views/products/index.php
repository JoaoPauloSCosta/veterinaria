<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Produtos</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal">Novo Produto</button>
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
  </div>
  <div class="col-md-4">
    <div class="card mb-3"><div class="card-body">
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
        <div class="mb-2"><label class="form-label">Quantidade</label><input class="form-control" name="quantity" type="number" required></div>
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
        <div class="mb-2"><label class="form-label">Quantidade</label><input class="form-control" name="quantity" type="number" required></div>
        <div class="text-end"><button class="btn btn-warning">Registrar</button></div>
      </form>
    </div></div>

    <div class="card mt-3"><div class="card-body">
      <h6>Estoque crítico</h6>
      <ul class="list-group list-group-flush">
        <?php foreach (($low ?? []) as $it): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($it['name']) ?>
            <span class="badge bg-danger"><?= e($it['stock_quantity']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div></div>
  </div>
</div>

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
