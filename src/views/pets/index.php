<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Pets</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#petModal">Novo Pet</button>
</div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger"><?= e($flash_error) ?></div>
<?php endif; ?>
<?php /* Sucesso exibido via modal; removido alerta para padronização com Veterinários */ ?>
<form class="row g-2 mb-3" method="get" action="<?= e(APP_URL) ?>/pets">
  <div class="col-auto">
    <input type="text" class="form-control" name="q" placeholder="Buscar por pet ou cliente" value="<?= e($q ?? '') ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary">Buscar</button>
  </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
  <thead><tr>
    <th>ID</th><th>Nome</th><th>Cliente</th><th>Espécie</th><th>Raça</th><th>Nasc.</th><th class="text-end">Ações</th>
  </tr></thead>
  <tbody>
    <?php foreach (($pets ?? []) as $p): ?>
      <tr>
        <td><?= e($p['id']) ?></td>
        <td><?= e($p['name']) ?></td>
        <td><?= e($p['client_name'] ?? '') ?></td>
        <td><?= e($p['species']) ?></td>
        <td><?= e($p['breed']) ?></td>
        <td><?= e(br_date($p['birth_date'])) ?></td>
        <td class="text-end">
          <div class="d-inline-flex align-items-center gap-1">
            <a class="btn btn-sm btn-info" href="<?= e(APP_URL) ?>/records/<?= e($p['id']) ?>"><i class="fa-solid fa-notes-medical"></i> Prontuário</a>
            <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#petModalEdit<?= e($p['id']) ?>"><i class="fa-regular fa-pen-to-square"></i> Editar</button>
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#petDeleteConfirmModal" data-pet-id="<?= e($p['id']) ?>" data-pet-name="<?= e($p['name']) ?>">
              <i class="fa-regular fa-trash-can"></i> Excluir
            </button>
          </div>
        </td>
      </tr>
      <div class="modal fade" id="petModalEdit<?= e($p['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Pet</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <form method="post" action="<?= e(APP_URL) ?>/pets/<?= e($p['id']) ?>/edit">
                <?= csrf_input() ?>
                <div class="row g-3">
                  <div class="col-md-6"><label class="form-label">Tutor (Cliente)</label>
                    <select name="client_id" class="form-select" required>
                      <?php foreach (($clients ?? []) as $cl): ?>
                        <option value="<?= e($cl['id']) ?>" <?= ($cl['id'] == $p['client_id']) ? 'selected' : '' ?>><?= e($cl['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" value="<?= e($p['name']) ?>" required></div>
                  <div class="col-md-4"><label class="form-label">Espécie</label><input name="species" class="form-control" value="<?= e($p['species']) ?>"></div>
                  <div class="col-md-4"><label class="form-label">Raça</label><input name="breed" class="form-control" value="<?= e($p['breed']) ?>"></div>
                  <div class="col-md-4"><label class="form-label">Nascimento (dd/mm/aaaa)</label><input name="birth_date" class="form-control" value="<?= e(br_date($p['birth_date'])) ?>" placeholder="01/01/2000" maxlength="10"></div>
                  <div class="col-md-6"><label class="form-label">Sexo</label><input name="gender" class="form-control" value="<?= e($p['gender']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">Cor</label><input name="color" class="form-control" value="<?= e($p['color']) ?>"></div>
                  <div class="col-12"><label class="form-label">Observações</label><textarea name="notes" class="form-control"><?= e($p['notes']) ?></textarea></div>
                </div>
                <div class="mt-3 text-end"><button class="btn btn-primary">Salvar</button></div>
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

<!-- Modal de Sucesso (Pet) -->
<div class="modal fade" id="petSuccessModal" tabindex="-1" aria-hidden="true">
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

<!-- Modal Confirmar Exclusão de Pet -->
<div class="modal fade" id="petDeleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fa-regular fa-trash-can me-2"></i>Confirmar exclusão</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja excluir o pet <strong data-pet-name-target></strong>?</p>
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

<script>
  // Preencher e abrir modal de confirmação de exclusão de pet
  document.addEventListener('DOMContentLoaded', function(){
    var petDeleteModal = document.getElementById('petDeleteConfirmModal');
    if (petDeleteModal) {
      petDeleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        if (!button) return;
        var petId = button.getAttribute('data-pet-id');
        var petName = button.getAttribute('data-pet-name') || '';
        petDeleteModal.querySelector('[data-pet-name-target]').textContent = petName;
        var form = petDeleteModal.querySelector('form');
        form.setAttribute('action', '<?= e(APP_URL) ?>/pets/' + petId + '/delete');
      });
    }
    // Autoabrir modal de sucesso
    <?php if (!empty($flash_success ?? '')): ?>
      var smEl = document.getElementById('petSuccessModal');
      if (smEl) { (new bootstrap.Modal(smEl)).show(); }
    <?php endif; ?>
  });
</script>
<?php endif; ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert[data-autohide="true"]').forEach(function(el){
      setTimeout(function(){ el.classList.add('d-none'); }, 5000);
    });
  });
</script>

<!-- Modal Novo Pet -->
<div class="modal fade" id="petModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Novo Pet</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/pets/create">
          <?= csrf_input() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Tutor (Cliente)</label>
              <select name="client_id" class="form-select" required>
                <option value="">Selecione...</option>
                <?php foreach (($clients ?? []) as $cl): ?>
                  <option value="<?= e($cl['id']) ?>"><?= e($cl['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" required></div>
            <div class="col-md-4"><label class="form-label">Espécie</label><input name="species" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Raça</label><input name="breed" class="form-control"></div>
            <div class="col-md-4"><label class="form-label">Nascimento (dd/mm/aaaa)</label><input name="birth_date" class="form-control" placeholder="01/01/2000" maxlength="10"></div>
            <div class="col-md-6"><label class="form-label">Sexo</label><input name="gender" class="form-control"></div>
            <div class="col-md-6"><label class="form-label">Cor</label><input name="color" class="form-control"></div>
            <div class="col-12"><label class="form-label">Observações</label><textarea name="notes" class="form-control"></textarea></div>
          </div>
          <div class="mt-3 text-end"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
