<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Pets</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#petModal">Novo Pet</button>
</div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger"><?= e($flash_error) ?></div>
<?php endif; ?>
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
            <form action="<?= e(APP_URL) ?>/pets/<?= e($p['id']) ?>/delete" method="post" class="d-inline m-0" onsubmit="return confirm('Excluir pet?');">
              <?= csrf_input() ?>
              <button class="btn btn-sm btn-danger"><i class="fa-regular fa-trash-can"></i> Excluir</button>
            </form>
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
                  <div class="col-md-4"><label class="form-label">Nascimento (dd/mm/aaaa)</label><input name="birth_date" class="form-control" value="<?= e(br_date($p['birth_date'])) ?>" placeholder="dd/mm/aaaa"></div>
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
            <div class="col-md-4"><label class="form-label">Nascimento (dd/mm/aaaa)</label><input name="birth_date" class="form-control" placeholder="dd/mm/aaaa"></div>
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
