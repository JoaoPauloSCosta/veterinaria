<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Veterinários</h3>
  <div class="d-flex gap-2">
    <button class="btn btn-primary btn-top" data-bs-toggle="modal" data-bs-target="#vetModal">Novo Veterinário</button>
  </div>
  </div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger" role="alert" data-autohide="true"><?= e($flash_error) ?></div>
<?php endif; ?>
<?php if (!empty($flash_success)): ?>
  <div class="alert alert-success" role="alert" data-autohide="true"><?= e($flash_success) ?></div>
<?php endif; ?>
<form class="row g-2 mb-3" method="get" action="<?= e(APP_URL) ?>/veterinarians">
  <div class="col-auto">
    <input type="text" class="form-control" name="q" placeholder="Buscar por nome ou email" value="<?= e($q ?? '') ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary">Buscar</button>
  </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
  <thead><tr>
    <th>ID</th><th>Nome</th><th>Email</th><th>Especialidades</th><th>Ativo</th><th class="text-end">Ações</th>
  </tr></thead>
  <tbody>
    <?php $modals = ''; ?>
    <?php foreach (($vets ?? []) as $v): ?>
      <tr>
        <td><?= e($v['id']) ?></td>
        <td><?= e($v['name']) ?></td>
        <td><?= e($v['email']) ?></td>
        <?php
          $specsRaw = $v['specialties'] ?? '';
          $specs = is_array($specsRaw) ? $specsRaw : ((is_string($specsRaw) && $specsRaw !== '') ? array_map('trim', explode(',', $specsRaw)) : []);
          $shown = array_slice($specs, 0, 2);
          $more = max(count($specs) - 2, 0);
        ?>
        <td><?= $shown ? implode(', ', array_map('e', $shown)) . ($more > 0 ? ' +' . e((string)$more) : '') : '—' ?></td>
        <td><?= !empty($v['is_active']) ? 'Sim' : 'Não' ?></td>
        <td class="text-end">
          <div class="d-inline-flex align-items-center gap-1">
            <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#vetModalEdit<?= e($v['id']) ?>"><i class="fa-regular fa-pen-to-square"></i> Editar</button>
            <form action="<?= e(APP_URL) ?>/veterinarians/<?= e($v['id']) ?>/delete" method="post" class="d-inline m-0" onsubmit="return confirm('Excluir veterinário?');">
              <?= csrf_input() ?>
              <button class="btn btn-sm btn-danger"><i class="fa-regular fa-trash-can"></i> Excluir</button>
            </form>
          </div>
        </td>
      </tr>
      <?php ob_start(); ?>
      <div class="modal fade" id="vetModalEdit<?= e($v['id']) ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Editar Veterinário</h5>
              <button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
              <form method="post" action="<?= e(APP_URL) ?>/veterinarians/<?= e($v['id']) ?>/edit">
                <?= csrf_input() ?>
                <div class="row g-3">
                  <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" required value="<?= e($v['name']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="<?= e($v['email']) ?>"></div>
                  <div class="col-md-6"><label class="form-label">Senha (opcional)</label><input type="password" name="password" class="form-control" placeholder="Mín. 6 caracteres"></div>
                  <div class="col-md-6 d-flex align-items-center"><div class="form-check mt-4">
                     <input class="form-check-input" type="checkbox" name="is_active" id="vetActive<?= e($v['id']) ?>" <?= !empty($v['is_active']) ? 'checked' : '' ?>>
                     <label class="form-check-label" for="vetActive<?= e($v['id']) ?>">Ativo</label>
                   </div></div>
                  <div class="col-md-12"><label class="form-label">Especialidades</label>
                    <select name="specialties[]" class="form-select" multiple size="6">
                      <?php foreach (($specialties ?? []) as $s): $sid = (int)$s['id']; $selected = in_array($sid, ($v['specialty_ids'] ?? []), true); ?>
                        <option value="<?= e((string)$sid) ?>" <?= $selected ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Segure Ctrl (Windows) para múltiplas seleções.</small>
                  </div>
                  <div class="col-md-6"><label class="form-label">Celular</label><input name="mobile_phone" class="form-control" value="<?= e($v['mobile_phone'] ?? '') ?>" placeholder="(DD) 9XXXX-XXXX"></div>
                  <div class="col-md-6"><label class="form-label">Telefone Fixo</label><input name="landline_phone" class="form-control" value="<?= e($v['landline_phone'] ?? '') ?>" placeholder="(DD) XXXX-XXXX"></div>
                  <div class="col-md-6"><label class="form-label">Email Profissional</label><input type="email" name="professional_email" class="form-control" value="<?= e($v['professional_email'] ?? '') ?>" placeholder="nome@empresa.com"></div>
                  <div class="col-md-6"><label class="form-label">CRMV</label><input name="crmv" class="form-control" value="<?= e($v['crmv'] ?? '') ?>" placeholder="12345"></div>
                  <div class="col-md-3"><label class="form-label">UF</label><input name="crmv_uf" maxlength="2" class="form-control" value="<?= e($v['crmv_uf'] ?? '') ?>" placeholder="SP"></div>
                  <div class="col-md-3"><label class="form-label">Tipo de Contrato</label>
                    <select name="employment_type" class="form-select">
                      <option value="">—</option>
                      <option value="CLT" <?= (($v['employment_type'] ?? '') === 'CLT') ? 'selected' : '' ?>>CLT</option>
                      <option value="PJ" <?= (($v['employment_type'] ?? '') === 'PJ') ? 'selected' : '' ?>>PJ</option>
                    </select>
                  </div>
                  <div class="col-md-3"><label class="form-label">Admissão</label><input type="text" name="admission_date" class="form-control" value="<?= e($v['admission_date'] ? br_date($v['admission_date']) : '') ?>" placeholder="dd/mm/aaaa" maxlength="10"></div>
                  <div class="col-md-3"><label class="form-label">Salário</label><input type="number" step="0.01" name="salary" class="form-control" value="<?= e($v['salary'] ?? '') ?>" placeholder="0,00"></div>
                  <div class="col-md-3"><label class="form-label">Carga horária (h/sem)</label><input type="number" name="workload_hours" class="form-control" value="<?= e($v['workload_hours'] ?? '') ?>" placeholder="40"></div>
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert[data-autohide="true"]').forEach(function(el){
      setTimeout(function(){ el.classList.add('d-none'); }, 5000);
    });
  });
</script>

<?= $modals ?>

<!-- Modal Novo Veterinário -->
<div class="modal fade" id="vetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Novo Veterinário</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/veterinarians/create">
          <?= csrf_input() ?>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome</label><input name="name" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-md-6"><label class="form-label">Senha</label><input type="password" name="password" class="form-control" required placeholder="Mín. 6 caracteres"></div>
            <div class="col-md-6 d-flex align-items-center"><div class="form-check mt-4">
              <input class="form-check-input" type="checkbox" name="is_active" id="vetActiveNew" checked>
              <label class="form-check-label" for="vetActiveNew">Ativo</label>
            </div></div>
            <div class="col-md-12"><label class="form-label">Especialidades</label>
              <select name="specialties[]" class="form-select" multiple size="6">
                <?php foreach (($specialties ?? []) as $s): $sid = (int)$s['id']; ?>
                  <option value="<?= e((string)$sid) ?>"><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Segure Ctrl (Windows) para múltiplas seleções.</small>
            </div>
            <div class="col-md-6"><label class="form-label">Celular</label><input name="mobile_phone" class="form-control" placeholder="(DD) 9XXXX-XXXX"></div>
            <div class="col-md-6"><label class="form-label">Telefone Fixo</label><input name="landline_phone" class="form-control" placeholder="(DD) XXXX-XXXX"></div>
            <div class="col-md-6"><label class="form-label">Email Profissional</label><input type="email" name="professional_email" class="form-control" placeholder="nome@empresa.com"></div>
            <div class="col-md-6"><label class="form-label">CRMV</label><input name="crmv" class="form-control" placeholder="12345"></div>
            <div class="col-md-3"><label class="form-label">UF</label><input name="crmv_uf" maxlength="2" class="form-control" placeholder="SP"></div>
            <div class="col-md-3"><label class="form-label">Tipo de Contrato</label>
              <select name="employment_type" class="form-select">
                <option value="">—</option>
                <option value="CLT">CLT</option>
                <option value="PJ">PJ</option>
              </select>
            </div>
            <div class="col-md-3"><label class="form-label">Admissão</label><input type="text" name="admission_date" class="form-control" placeholder="dd/mm/aaaa" maxlength="10"></div>
            <div class="col-md-3"><label class="form-label">Salário</label><input type="number" step="0.01" name="salary" class="form-control" placeholder="0,00"></div>
            <div class="col-md-3"><label class="form-label">Carga horária (h/sem)</label><input type="number" name="workload_hours" class="form-control" placeholder="40"></div>
          </div>
          <div class="mt-3 text-end"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>