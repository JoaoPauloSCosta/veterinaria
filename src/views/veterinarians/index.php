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
<?php /* Sucesso será exibido via modal abaixo; removemos o alerta visual */ ?>
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
            <button type="button"
                    class="btn btn-sm btn-danger btn-delete-vet"
                    data-action="<?= e(APP_URL) ?>/veterinarians/<?= e($v['id']) ?>/delete"
                    data-vet-name="<?= e($v['name']) ?>">
              <i class="fa-regular fa-trash-can"></i> Excluir
            </button>
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
                  <div class="col-md-6"><label class="form-label">Senha (opcional)</label><input type="password" name="password" class="form-control" placeholder="Mín. 6 caracteres"><small class="text-muted">*Deixe o campo em branco para manter a senha atual.</small></div>
                       
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
    // INTERCEPTAR E BLOQUEAR QUALQUER POPUP DE CONFIRMAÇÃO
    // Sobrescrever funções globais para evitar popups
    window.confirm = function(message) {
      console.log('Popup bloqueado:', message);
      return false; // Sempre retorna false para cancelar ações
    };
    
    window.alert = function(message) {
      console.log('Alert bloqueado:', message);
      return false;
    };

    // Autohide apenas para alertas de erro, já que sucesso vai para modal
    document.querySelectorAll('.alert.alert-danger[data-autohide="true"]').forEach(function(el){
      setTimeout(function(){ el.classList.add('d-none'); }, 5000);
    });

    // Modal de confirmação de exclusão
    var deleteModalEl = document.getElementById('vetDeleteConfirmModal');
    var deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
    var deleteForm = document.getElementById('vetDeleteForm');
    var pendingDeleteAction = null;

    // Interceptar TODOS os cliques em botões de exclusão
    document.addEventListener('click', function(e) {
      // Verificar se é um botão de exclusão de veterinário
      if (e.target.classList.contains('btn-delete-vet') || 
          e.target.closest('.btn-delete-vet')) {
        
        e.preventDefault(); // Impedir ação padrão
        e.stopPropagation(); // Impedir propagação
        
        var btn = e.target.classList.contains('btn-delete-vet') ? 
                  e.target : e.target.closest('.btn-delete-vet');
        
        var name = btn.getAttribute('data-vet-name') || '';
        var action = btn.getAttribute('data-action') || '';
        pendingDeleteAction = action;
        var nameEl = document.getElementById('confirmVetName');
        if (nameEl) { nameEl.textContent = name; }
        if (deleteModal) { deleteModal.show(); }
        
        return false;
      }
    }, true); // Usar capture para interceptar antes de outros handlers

    var confirmBtn = document.getElementById('btnConfirmDeleteVet');
    if (confirmBtn) {
      confirmBtn.addEventListener('click', function(){
        if (deleteForm && pendingDeleteAction) {
          deleteForm.setAttribute('action', pendingDeleteAction);
          deleteForm.submit();
        }
      });
    }

    // Modal de sucesso: criado/excluído
    var successMsg = <?= isset($flash_success) && $flash_success ? json_encode($flash_success, JSON_UNESCAPED_UNICODE) : 'null' ?>;
    if (successMsg) {
      var successModalEl = document.getElementById('vetSuccessModal');
      var successModal = successModalEl ? new bootstrap.Modal(successModalEl) : null;
      var msgEl = document.getElementById('vetSuccessMessage');
      if (msgEl) { msgEl.textContent = successMsg; }
      if (successModal) { successModal.show(); }
    }

    // Exibir o popup de conflito também quando houver erro geral na exclusão
    var errorMsg = <?= isset($flash_error) && $flash_error ? json_encode($flash_error, JSON_UNESCAPED_UNICODE) : 'null' ?>;
    if (errorMsg) {
      var conflictModalElErr = document.getElementById('vetConflictModal');
      var conflictModalErr = conflictModalElErr ? new bootstrap.Modal(conflictModalElErr) : null;
      if (conflictModalErr) {
        conflictModalErr.show();
        // Garantir visibilidade por 10s: desabilitar botões de fechar temporariamente
        var headerCloseBtnErr = document.getElementById('vetConflictCloseHeaderBtn');
        var footerCloseBtnErr = document.getElementById('vetConflictCloseBtn');
        [headerCloseBtnErr, footerCloseBtnErr].forEach(function(btn){ if (btn) btn.disabled = true; });
        setTimeout(function(){
          [headerCloseBtnErr, footerCloseBtnErr].forEach(function(btn){ if (btn) btn.disabled = false; });
        }, 10000);
      }
    }

    // Modal de conflitos de agendamentos futuros (abrir somente após tentativa de exclusão)
    var conflicts = <?= isset($futureAppointments) && is_array($futureAppointments) && count($futureAppointments) > 0 ? json_encode($futureAppointments, JSON_UNESCAPED_UNICODE) : '[]' ?>;
    // Usa a flag vinda do controller (showConflictModal) para decidir abertura
    var vetConflictShow = <?= !empty($showConflictModal) ? 'true' : 'false' ?>;
    if (vetConflictShow && conflicts.length > 0) {
      var conflictModalEl = document.getElementById('vetConflictModal');
      var conflictModal = conflictModalEl ? new bootstrap.Modal(conflictModalEl) : null;
      if (conflictModal) {
        conflictModal.show();
        // Manter visível por 10s: desabilitar botões de fechar temporariamente
        var headerCloseBtn = document.getElementById('vetConflictCloseHeaderBtn');
        var footerCloseBtn = document.getElementById('vetConflictCloseBtn');
        [headerCloseBtn, footerCloseBtn].forEach(function(btn){ if (btn) btn.disabled = true; });
        setTimeout(function(){
          [headerCloseBtn, footerCloseBtn].forEach(function(btn){ if (btn) btn.disabled = false; });
        }, 10000);
      }
    }
  });
</script>

<?= $modals ?>

<!-- Modal de Confirmação de Exclusão (reutilizável) -->
<div class="modal fade" id="vetDeleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="fa-regular fa-trash-can me-2"></i>Confirmar exclusão</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja excluir o veterinário <strong id="confirmVetName"></strong>?</p>
        <p class="text-muted mb-0">Esta ação é irreversível.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDeleteVet">Excluir</button>
      </div>
    </div>
  </div>
  <!-- Formulário oculto usado para submissão da exclusão com CSRF -->
  <form id="vetDeleteForm" method="post" action="" class="d-none">
    <?= csrf_input() ?>
  </form>
  
</div>

<!-- Modal de Sucesso (criação/exclusão) -->
<div class="modal fade" id="vetSuccessModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fa-regular fa-circle-check me-2"></i>Operação concluída</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p id="vetSuccessMessage" class="mb-0"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
  
</div>

<!-- Modal Conflito: Veterinário possui agendamentos vinculados -->
<div class="modal fade" id="vetConflictModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-no-refresh="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fa-solid fa-ban me-2"></i>Exclusão não permitida</h5>
        <button class="btn-close" data-bs-dismiss="modal" id="vetConflictCloseHeaderBtn"></button>
      </div>
      <div class="modal-body">
        <div>
          <p class="mb-1">O veterinário selecionado não pode ser excluído pois:</p>
          <ul class="mb-0">
            <li>Possui agendamentos em aberto em seu nome</li>
            <li>Ou está cadastrado como ativo no sistema</li>
          </ul>
          <p class="mt-2 mb-0">Verifique essas condições e tente novamente.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="vetConflictCloseBtn">Fechar</button>
        <a class="btn btn-primary" href="<?= e(APP_URL) ?>/agenda?vet=<?= e($conflict_vet_id ?? 0) ?>" id="viewVetSchedule">Ver agenda do veterinário</a>
      </div>
    </div>
  </div>
</div>
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