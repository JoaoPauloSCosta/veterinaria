<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Clientes</h3>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary btn-top" href="<?= e(APP_URL) ?>/export/csv">Exportar CSV</a>
    <button class="btn btn-outline-primary btn-top" data-bs-toggle="modal" data-bs-target="#importModal">Importar CSV</button>
    <button class="btn btn-primary btn-top" data-bs-toggle="modal" data-bs-target="#clientModal">Novo Cliente</button>
  </div>
</div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger"><?= e($flash_error) ?></div>
<?php endif; ?>
<?php /* Sucesso exibido via modal; removido alerta para padronização com Veterinários */ ?>
<form class="row g-2 mb-3" method="get" action="<?= e(APP_URL) ?>/clients">
  <div class="col-auto">
    <input type="text" class="form-control" name="q" placeholder="Buscar por nome, email ou CPF" value="<?= e($q ?? '') ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary">Buscar</button>
  </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
  <thead><tr>
    <th>ID</th><th>Nome</th><th>CPF/CNPJ</th><th>Email</th><th>Telefone</th><th>Telefone Fixo</th><th class="text-end">Ações</th>
  </tr></thead>
  <tbody>
    <?php $modals = ''; ?>
    <?php foreach (($clients ?? []) as $c): ?>
      <tr>
        <td><?= e($c['id']) ?></td>
        <td><?= e($c['name']) ?></td>
        <td><?= e($c['cpf_cnpj']) ?></td>
        <td><?= e($c['email']) ?></td>
        <td><?= e($c['phone']) ?></td>
        <td><?= e($c['landline_phone']) ?></td>
        <td class="text-end">
          <div class="d-inline-flex align-items-center gap-1">
            <a class="btn btn-sm btn-info" href="<?= e(APP_URL) ?>/pets?client_id=<?= e($c['id']) ?>"><i class="fa-solid fa-paw"></i> Pets</a>
            <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#clientModalEdit<?= e($c['id']) ?>"><i class="fa-regular fa-pen-to-square"></i> Editar</button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#clientDeleteConfirmModal" data-client-id="<?= e($c['id']) ?>" data-client-name="<?= e($c['name']) ?>">
              <i class="fa-regular fa-trash-can"></i> Excluir
            </button>
          </div>
      </tr>
      <?php ob_start(); ?>
      <div class="modal fade" id="clientModalEdit<?= e($c['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Cliente</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <form method="post" action="<?= e(APP_URL) ?>/clients/<?= e($c['id']) ?>/edit">
                <?= csrf_input() ?>
                <div class="row g-3">
                  <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" required value="<?= e($c['name']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">CPF/CNPJ</label><input name="cpf_cnpj" class="form-control" value="<?= e($c['cpf_cnpj']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($c['email']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">Telefone Celular</label><input name="phone" class="form-control" value="<?= e($c['phone']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">Telefone Fixo</label><input name="landline_phone" class="form-control" value="<?= e($c['landline_phone']) ?>"></div>
                  <div class="col-12"><label class="form-label">Endereço</label><input name="address" class="form-control" value="<?= e($c['address']) ?>"></div>
                </div>
                <div class="mt-3 text-end"><button class="btn btn-primary">Salvar</button></div>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php $modals .= ob_get_clean(); ?>
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert[data-autohide="true"]').forEach(function(el){
      setTimeout(function(){ el.classList.add('d-none'); }, 5000);
    });
  });
</script>

<?= $modals ?>

<script>
  // Preencher e abrir modal de confirmação de exclusão
  document.addEventListener('DOMContentLoaded', function(){
    var deleteModal = document.getElementById('clientDeleteConfirmModal');
    if (deleteModal) {
      deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) return;
        var clientId = button.getAttribute('data-client-id');
        var clientName = button.getAttribute('data-client-name') || '';
        deleteModal.querySelector('[data-client-name-target]').textContent = clientName;
        var form = deleteModal.querySelector('form');
        form.setAttribute('action', '<?= e(APP_URL) ?>/clients/' + clientId + '/delete');
      });
    }
  });
</script>

<!-- Modal Novo Cliente -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Novo Cliente</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/clients/create">
          <?= csrf_input() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">CPF/CNPJ</label><input name="cpf_cnpj" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Telefone Celular</label><input name="phone" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Telefone Fixo</label><input name="landline_phone" class="form-control"></div>
            <div class="col-12"><label class="form-label">Endereço</label><input name="address" class="form-control"></div>
          </div>
          <div class="mt-3 text-end"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Importar CSV -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Importar Clientes (CSV)</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/import/csv" enctype="multipart/form-data">
          <?= csrf_input() ?>
          <p class="small text-muted">Cabeçalho esperado: <code>name;cpf_cnpj;email;phone;address</code></p>
          <input type="file" name="csv" accept=".csv,text/csv" class="form-control" required>
          <div class="text-end mt-3"><button class="btn btn-primary">Importar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Sucesso (Cliente) -->
<div class="modal fade" id="clientSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fa-regular fa-circle-check me-2"></i>Operação concluída</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-0"><?= e($flash_success ?? '') ?></p>
      </div>
      <div class="modal-footer"><button class="btn btn-success" data-bs-dismiss="modal">OK</button></div>
    </div>
  </div>
</div>

<!-- Modal Confirmar Exclusão de Cliente -->
<div class="modal fade" id="clientDeleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fa-regular fa-trash-can me-2"></i>Confirmar exclusão</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja excluir o cliente <strong data-client-name-target></strong>?</p>
        <p class="text-muted mb-0">Essa ação é permanente e não pode ser desfeita.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <form method="post" action="#">
          <?= csrf_input() ?>
          <button class="btn btn-danger">Excluir</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal Conflito: Cliente possui Pets vinculados -->
<?php if (!empty($linkedPets ?? []) && !empty($conflict_client_id ?? null)): ?>
<div class="modal fade" id="clientConflictModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fa-solid fa-ban me-2"></i>Exclusão não permitida</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>O cliente <strong><?= e($conflict_client_name ?? '') ?></strong> possui pets vinculados e não pode ser excluído.</p>
        <p class="text-muted">Para prosseguir, remova ou transfira os pets associados antes de tentar excluir o cliente.</p>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead><tr><th>#</th><th>Pet</th><th>Espécie</th><th>Raça</th></tr></thead>
            <tbody>
              <?php foreach (($linkedPets ?? []) as $lp): ?>
                <tr>
                  <td><?= e($lp['id']) ?></td>
                  <td><?= e($lp['name'] ?? '') ?></td>
                  <td><?= e($lp['species'] ?? '') ?></td>
                  <td><?= e($lp['breed'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fechar</button>
        <a class="btn btn-primary" href="<?= e(APP_URL) ?>/pets?client_id=<?= e($conflict_client_id ?? 0) ?>">Ver pets do cliente</a>
      </div>
    </div>
  </div>
</div>
<script>
  // Autoabrir modais de sucesso e conflito
  document.addEventListener('DOMContentLoaded', function(){
    <?php if (!empty($flash_success ?? '')): ?>
      var smEl = document.getElementById('clientSuccessModal');
      if (smEl) { (new bootstrap.Modal(smEl)).show(); }
    <?php endif; ?>
    var cmEl = document.getElementById('clientConflictModal');
    if (cmEl) { (new bootstrap.Modal(cmEl)).show(); }
  });
</script>
<?php endif; ?>
