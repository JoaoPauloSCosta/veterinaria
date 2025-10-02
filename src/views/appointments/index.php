<?php require_login(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Agenda</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agendaModal">Nova Consulta</button>
</div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger"><?= e($flash_error) ?></div>
<?php endif; ?>
<form class="row g-2 mb-3" method="get" action="<?= e(APP_URL) ?>/agenda">
  <div class="col-auto">
    <input type="date" class="form-control" name="from" value="<?= e($from ?? '') ?>">
  </div>
  <div class="col-auto">
    <input type="date" class="form-control" name="to" value="<?= e($to ?? '') ?>">
  </div>
  <div class="col-auto">
    <input type="number" class="form-control" name="vet" placeholder="ID do Vet" value="<?= e($vet ?? '') ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary">Filtrar</button>
  </div>
</form>
<div class="table-responsive">
<table class="table table-striped table-hover align-middle">
  <thead><tr>
    <th>ID</th><th>Pet</th><th>Veterinário</th><th>Início</th><th>Fim</th><th>Sala</th><th>Status</th><th class="text-end">Ações</th>
  </tr></thead>
  <tbody>
    <?php foreach (($items ?? []) as $a): ?>
      <tr>
        <td><?= e($a['id']) ?></td>
        <td><?= e($a['pet_name']) ?></td>
        <td><?= e($a['vet_name']) ?></td>
        <td><?= e(br_datetime($a['start_time'])) ?></td>
        <td><?= e(br_datetime($a['end_time'])) ?></td>
        <td><?= e($a['room']) ?></td>
        <td><span class="badge bg-secondary text-uppercase"><?= e($a['status']) ?></span></td>
        <td class="text-end">
          <form action="<?= e(APP_URL) ?>/agenda/<?= e($a['id']) ?>/cancel" method="post" class="d-inline" onsubmit="return confirm('Cancelar consulta?');">
            <?= csrf_input() ?>
            <button class="btn btn-sm btn-warning"><i class="fa-solid fa-ban"></i> Cancelar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<!-- Modal Nova Consulta -->
<div class="modal fade" id="agendaModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Agendar Consulta</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/agenda/create">
          <?= csrf_input() ?>
          <div class="mb-2"><label class="form-label">Pet</label>
            <select name="pet_id" class="form-select" required>
              <option value="">Selecione...</option>
              <?php foreach (($pets ?? []) as $pt): ?>
                <option value="<?= e($pt['id']) ?>"><?= e($pt['name']) ?> — <?= e($pt['client_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2"><label class="form-label">Veterinário</label>
            <select name="vet_id" class="form-select" required>
              <option value="">Selecione...</option>
              <?php foreach (($vets ?? []) as $v): ?>
                <option value="<?= e($v['id']) ?>"><?= e($v['name']) ?> (<?= e($v['email']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2"><label class="form-label">Início</label><input name="start_time" type="datetime-local" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Fim</label><input name="end_time" type="datetime-local" class="form-control" required></div>
          <div class="mb-2"><label class="form-label">Sala</label><input name="room" class="form-control"></div>
          <div class="mb-2"><label class="form-label">Observações</label><textarea name="notes" class="form-control"></textarea></div>
          <div class="text-end"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
