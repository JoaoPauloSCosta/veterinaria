<?php require_login(); ?>
<h3>Prontuário do Pet #<?= e($petId) ?></h3>
<div class="mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recordModal">Novo Atendimento</button>
</div>
<div class="table-responsive">
  <table class="table table-striped">
    <thead><tr><th>Data</th><th>Veterinário</th><th>Diagnóstico</th><th>Tratamento</th></tr></thead>
    <tbody>
      <?php foreach (($items ?? []) as $r): ?>
        <tr>
          <td><?= e($r['record_date']) ?></td>
          <td><?= e($r['vet_name']) ?></td>
          <td><?= e($r['diagnosis']) ?></td>
          <td><?= e($r['treatment']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="recordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Registrar Atendimento</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/records/<?= e($petId) ?>/create">
          <?= csrf_input() ?>
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Anamnese</label><textarea name="anamnesis" class="form-control"></textarea></div>
            <div class="col-12"><label class="form-label">Diagnóstico</label><textarea name="diagnosis" class="form-control"></textarea></div>
            <div class="col-12"><label class="form-label">Tratamento</label><textarea name="treatment" class="form-control"></textarea></div>
            <div class="col-12"><label class="form-label">Prescrição</label><textarea name="prescription" class="form-control"></textarea></div>
            <div class="col-12">
              <label class="form-label">Produtos usados (ID:Qtd;ID:Qtd ...)</label>
              <input type="text" class="form-control" name="used_raw" placeholder="ex: 2:1;4:3">
              <small class="text-muted">Produtos físicos terão baixa de estoque automática.</small>
            </div>
          </div>
          <div class="text-end mt-3"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
// Converte used_raw em estrutura used_products[] antes de enviar
const modal = document.getElementById('recordModal');
modal?.addEventListener('shown.bs.modal', () => {});
modal?.querySelector('form')?.addEventListener('submit', (e) => {
  const input = modal.querySelector('input[name="used_raw"]');
  const val = (input?.value || '').trim();
  if (!val) return;
  val.split(';').forEach(pair => {
    const [id, qty] = pair.split(':').map(s => s.trim());
    if (id && qty) {
      const pid = document.createElement('input');
      pid.type = 'hidden'; pid.name = 'used_products[][product_id]'; pid.value = id;
      e.target.appendChild(pid);
      const q = document.createElement('input');
      q.type = 'hidden'; q.name = 'used_products[][quantity]'; q.value = qty;
      e.target.appendChild(q);
    }
  });
});
</script>
