<?php require_login(); ?>
<?php
  // Documentação:
  // `$showAppointmentSuccessModal` é uma flag de sessão específica definida
  // pelo AppointmentsController ao criar um novo agendamento. Ela garante que
  // o modal verde de sucesso apareça APENAS após a criação e não durante
  // outras ações (ex.: cancelamento). Após lido, a flag é limpa para evitar
  // reexibição em reloads subsequentes.
  $showAppointmentSuccessModal = !empty($_SESSION['appointment_success']);
  if ($showAppointmentSuccessModal) { unset($_SESSION['appointment_success']); }
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Agenda</h3>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agendaModal">Nova Consulta</button>
</div>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger" role="alert" data-autohide="true"><?= e($flash_error) ?></div>
<?php endif; ?>
<?php if (!empty($flash_success)): ?>
  <div class="alert alert-success" role="alert" data-autohide="true"><?= e($flash_success) ?></div>
<?php endif; ?>
<form class="row g-2 mb-3" method="get" action="<?= e(APP_URL) ?>/agenda">
  <div class="col-auto">
    <input type="date" class="form-control" name="from" value="<?= e($from ?? '') ?>" placeholder="Data inicial">
  </div>
  <div class="col-auto">
    <input type="date" class="form-control" name="to" value="<?= e($to ?? '') ?>" placeholder="Data final">
  </div>
  <div class="col-auto">
    <select class="form-select" name="vet">
      <option value="">Todos os veterinários</option>
      <?php foreach (($vets ?? []) as $v): ?>
        <option value="<?= e($v['id']) ?>" <?= ($vet ?? null) == $v['id'] ? 'selected' : '' ?>><?= e($v['name']) ?> (<?= e($v['email']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-auto">
    <select class="form-select" name="order" title="Ordenação por data">
      <option value="DESC" <?= ($order ?? 'DESC') === 'DESC' ? 'selected' : '' ?>>
        <i class="fas fa-sort-amount-down"></i> Mais recentes primeiro
      </option>
      <option value="ASC" <?= ($order ?? 'DESC') === 'ASC' ? 'selected' : '' ?>>
        <i class="fas fa-sort-amount-up"></i> Mais antigos primeiro
      </option>
    </select>
  </div>
  <div class="col-auto">
    <button class="btn btn-outline-secondary" type="submit">
      <i class="fas fa-filter"></i> Filtrar
    </button>
  </div>
  <div class="col-auto">
    <a href="<?= e(APP_URL) ?>/agenda" class="btn btn-outline-secondary" title="Limpar filtros">
      <i class="fas fa-times"></i> Limpar
    </a>
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
          <form action="<?= e(APP_URL) ?>/agenda/<?= e($a['id']) ?>/cancel" method="post" class="d-inline cancel-form" data-appointment-id="<?= e($a['id']) ?>">
            <?= csrf_input() ?>
            <button type="button" class="btn btn-sm btn-danger text-white cancel-btn"><i class="fa-solid fa-ban"></i> Cancelar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php
// Incluir helper de paginação
require_once __DIR__ . '/../../helpers/pagination.php';

// Preparar parâmetros de query para preservar filtros
$queryParams = [];
if (!empty($from)) $queryParams['from'] = $from;
if (!empty($to)) $queryParams['to'] = $to;
if (!empty($vet)) $queryParams['vet'] = $vet;
if (!empty($order)) $queryParams['order'] = $order;

// Renderizar paginação
echo render_pagination($page ?? 1, $totalPages ?? 1, $limit ?? 10, $total ?? 0, $queryParams);
?>

</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts
    document.querySelectorAll('.alert[data-autohide="true"]').forEach(function(el){
      setTimeout(function(){ el.classList.add('d-none'); }, 5000);
    });
    
    // Exibir modal verde de sucesso APÓS AGENDAR.
    // Lê a flag de sessão enviada pelo controller; se verdadeira,
    // instancia e mostra o `#appointmentSuccessModal`. Não interfere
    // nos demais fluxos (listagem, filtros, cancelamentos).
    const shouldShowSuccess = <?php echo $showAppointmentSuccessModal ? 'true' : 'false'; ?>;
    if (shouldShowSuccess) {
      const successModal = new bootstrap.Modal(document.getElementById('appointmentSuccessModal'));
      successModal.show();
    }
    
    // Auto-submit form when order changes
    const orderSelect = document.querySelector('select[name="order"]');
    if (orderSelect) {
      orderSelect.addEventListener('change', function() {
        // Submit the form automatically when order changes
        this.closest('form').submit();
      });
    }
    
    // Add visual feedback for current sort order
    const currentOrder = orderSelect ? orderSelect.value : 'DESC';
    const tableHeader = document.querySelector('th:nth-child(4)'); // "Início" column
    if (tableHeader) {
      const icon = currentOrder === 'DESC' ? 
        '<i class="fas fa-sort-down ms-1 text-primary"></i>' : 
        '<i class="fas fa-sort-up ms-1 text-primary"></i>';
      tableHeader.innerHTML += icon;
      tableHeader.style.cursor = 'pointer';
      tableHeader.title = 'Clique para alterar ordenação';
      
      // Allow clicking on header to toggle sort
      tableHeader.addEventListener('click', function() {
        const newOrder = currentOrder === 'DESC' ? 'ASC' : 'DESC';
        orderSelect.value = newOrder;
        orderSelect.dispatchEvent(new Event('change'));
      });
    }

    // Submissão do formulário de agendamento via AJAX.
    // Lógica: anula submit padrão, anexa `ajax=1`, desabilita botão
    // com feedback visual, envia para `/agenda/create` e trata retorno.
    // Em sucesso: fecha modal e recarrega (a view exibirá o modal de sucesso
    // via flag de sessão). Em erro: exibe modal de conflito com a mensagem.
    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
      appointmentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('ajax', '1'); // Indicar que é uma requisição AJAX
        
        // Desabilitar botão de submit para evitar múltiplos envios
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Salvando...';
        
        fetch('<?= e(APP_URL) ?>/agenda/create', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Fechar modal e recarregar página
            const modal = bootstrap.Modal.getInstance(document.getElementById('agendaModal'));
            modal.hide();
            location.reload();
          } else {
            // Exibir modal de conflito
            document.getElementById('conflictMessage').textContent = data.error;
            const conflictModal = new bootstrap.Modal(document.getElementById('conflictModal'));
            conflictModal.show();
            
            // Fechar modal de agendamento
            const agendaModal = bootstrap.Modal.getInstance(document.getElementById('agendaModal'));
            agendaModal.hide();
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          // Exibir modal de conflito com mensagem genérica
          document.getElementById('conflictMessage').textContent = 'Erro de rede. Tente novamente.';
          const conflictModal = new bootstrap.Modal(document.getElementById('conflictModal'));
          conflictModal.show();
          
          // Fechar modal de agendamento
          const agendaModal = bootstrap.Modal.getInstance(document.getElementById('agendaModal'));
          agendaModal.hide();
        })
        .finally(() => {
          // Reabilitar botão
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalText;
        });
      });
    }

    // Handle cancel appointment buttons
    let currentCancelForm = null;
    
    document.querySelectorAll('.cancel-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        currentCancelForm = this.closest('.cancel-form');
        
        // Show confirmation modal
        const confirmModal = new bootstrap.Modal(document.getElementById('cancelConfirmModal'));
        confirmModal.show();
      });
    });
    
    // Handle confirm cancel button
    document.getElementById('confirmCancelBtn').addEventListener('click', function() {
      if (currentCancelForm) {
        // Disable button and show loading
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Cancelando...';
        
        // Submit the form via AJAX
        const formData = new FormData(currentCancelForm);
        
        fetch(currentCancelForm.action, {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Close confirmation modal
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('cancelConfirmModal'));
            confirmModal.hide();
            
            // Show success modal
            const successModal = new bootstrap.Modal(document.getElementById('cancelSuccessModal'));
            successModal.show();
            
            // Reload page after success modal is closed
            document.getElementById('cancelSuccessModal').addEventListener('hidden.bs.modal', function() {
              location.reload();
            }, { once: true });
          } else {
            // Close confirmation modal
            const confirmModal = bootstrap.Modal.getInstance(document.getElementById('cancelConfirmModal'));
            confirmModal.hide();
            
            // Show error modal instead of alert
            document.getElementById('cancelErrorMessage').textContent = data.error || 'Erro ao cancelar agendamento';
            const errorModal = new bootstrap.Modal(document.getElementById('cancelErrorModal'));
            errorModal.show();
          }
        })
        .catch(error => {
          console.error('Erro:', error);
          alert('Erro ao cancelar agendamento. Tente novamente.');
        })
        .finally(() => {
          // Re-enable button
          this.disabled = false;
          this.innerHTML = '<i class="fas fa-ban me-1"></i>OK, Cancelar Consulta';
        });
      }
    });

    // Melhorar o botão "Alterar Horário" no modal de conflito
    const conflictModal = document.getElementById('conflictModal');
    if (conflictModal) {
      conflictModal.addEventListener('hidden.bs.modal', function() {
        // Quando o modal de conflito for fechado, reabrir o modal de agendamento
        const alterarBtn = this.querySelector('.btn-primary');
        if (alterarBtn && alterarBtn.dataset.reopenModal) {
          setTimeout(() => {
            const agendaModal = new bootstrap.Modal(document.getElementById('agendaModal'));
            agendaModal.show();
          }, 300);
          delete alterarBtn.dataset.reopenModal;
        }
      });
    }
  });
</script>

<!-- Modal Sucesso de Agendamento -->
<div class="modal fade" id="appointmentSuccessModal" tabindex="-1" aria-labelledby="appointmentSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="appointmentSuccessModalLabel">
          <i class="fas fa-check-circle me-2"></i>Sucesso
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="mb-2">Agendamento realizado com sucesso</h6>
            <p class="mb-0 text-muted">A consulta foi cadastrada conforme as regras do sistema.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
          <i class="fas fa-check me-1"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Nova Consulta -->
<div class="modal fade" id="agendaModal" tabindex="-1" aria-hidden="true" data-no-refresh="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Agendar Consulta</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="appointmentForm" method="post" action="<?= e(APP_URL) ?>/agenda/create">
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
          <div class="text-end">
            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Conflito de Horário -->
<div class="modal fade" id="conflictModal" tabindex="-1" aria-labelledby="conflictModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-no-refresh="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="conflictModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Conflito de Horário
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="conflictHeaderCloseBtn"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <div class="flex-shrink-0">
            <i class="fas fa-calendar-times text-danger" style="font-size: 2rem;"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <p class="mb-0" id="conflictMessage">
              Mensagem de conflito será exibida aqui.
            </p>
          </div>
        </div>
        <div class="alert alert-warning mb-0">
          <i class="fas fa-info-circle me-2"></i>
          <strong>Sugestão:</strong> Verifique a agenda do veterinário e escolha um horário disponível.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="conflictCloseBtn">
          <i class="fas fa-times me-1"></i>Fechar
        </button>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" onclick="this.dataset.reopenModal = 'true';">
          <i class="fas fa-edit me-1"></i>Alterar Horário
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Confirmação de Cancelamento -->
<div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-labelledby="cancelConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="cancelConfirmModalLabel">
          <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Cancelamento
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center mb-3">
          <div class="flex-shrink-0">
            <i class="fas fa-ban text-warning" style="font-size: 2.5rem;"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <p class="mb-2 fw-bold">Tem certeza que deseja cancelar esta consulta?</p>
            <p class="mb-0 text-muted">Esta ação não pode ser desfeita. O agendamento será marcado como cancelado.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>Cancelar
        </button>
        <button type="button" class="btn btn-warning" id="confirmCancelBtn">
          <i class="fas fa-ban me-1"></i>OK, Cancelar Consulta
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Sucesso de Cancelamento -->
<div class="modal fade" id="cancelSuccessModal" tabindex="-1" aria-labelledby="cancelSuccessModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="cancelSuccessModalLabel">
          <i class="fas fa-check-circle me-2"></i>Cancelamento Realizado
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="mb-2">Agendamento cancelado com sucesso!</h6>
            <p class="mb-0 text-muted">O status da consulta foi atualizado para "cancelado".</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">
          <i class="fas fa-check me-1"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Erro de Cancelamento -->
<div class="modal fade" id="cancelErrorModal" tabindex="-1" aria-labelledby="cancelErrorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="cancelErrorModalLabel">
          <i class="fas fa-exclamation-circle me-2"></i>Erro no Cancelamento
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center">
          <div class="flex-shrink-0">
            <i class="fas fa-times-circle text-danger" style="font-size: 3rem;"></i>
          </div>
          <div class="flex-grow-1 ms-3">
            <h6 class="mb-2" id="cancelErrorTitle">Erro</h6>
            <p class="mb-0 text-muted" id="cancelErrorMessage">Mensagem de erro será exibida aqui.</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i>OK
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  // Controle de recarga apenas após fechamento manual do modal de conflito
  document.addEventListener('DOMContentLoaded', function() {
    let conflictClosedManually = false;

    const conflictModalEl = document.getElementById('conflictModal');
    const conflictCloseBtn = document.getElementById('conflictCloseBtn');
    const conflictHeaderCloseBtn = document.getElementById('conflictHeaderCloseBtn');

    if (conflictCloseBtn) {
      conflictCloseBtn.addEventListener('click', function() {
        conflictClosedManually = true;
      });
    }

    if (conflictHeaderCloseBtn) {
      conflictHeaderCloseBtn.addEventListener('click', function() {
        conflictClosedManually = true;
      });
    }

    if (conflictModalEl) {
      conflictModalEl.addEventListener('hidden.bs.modal', function() {
        // Se usuário clicou em "Alterar Horário", reabre o modal de agendamento
        const alterarBtn = this.querySelector('.btn-primary');
        if (alterarBtn && alterarBtn.dataset.reopenModal) {
          setTimeout(() => {
            const agendaModal = new bootstrap.Modal(document.getElementById('agendaModal'));
            agendaModal.show();
          }, 300);
          delete alterarBtn.dataset.reopenModal;
          return;
        }

        // Se usuário fechou manualmente, recarrega a página
        if (conflictClosedManually) {
          conflictClosedManually = false; // reset flag
          location.reload();
        }
      });
    }
  });
</script>
        </button>
      </div>
    </div>
  </div>
</div>
