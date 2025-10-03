<?php
// Lista e formulário de veterinários com campos de perfil, especialidades e unidades
?>
<div class="container my-4">
  <h2>Veterinários</h2>

  <?php if (!empty($flash_success)): ?>
    <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($flash_success); ?></div>
  <?php endif; ?>
  <?php if (!empty($flash_error ?? '')): ?>
    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($flash_error); ?></div>
  <?php endif; ?>

  <form class="row g-2 mb-3" method="get" action="<?php echo APP_URL; ?>/veterinarians">
    <div class="col-auto">
      <input type="text" name="q" class="form-control" placeholder="Pesquisar por nome ou e-mail" value="<?php echo htmlspecialchars($q ?? ''); ?>">
    </div>
    <div class="col-auto">
      <button class="btn btn-primary" type="submit">Pesquisar</button>
    </div>
    <div class="col-auto ms-auto">
      <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#newVeterinarianModal">Novo Veterinário</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>Nome</th>
          <th>E-mail</th>
          <th>Especialidades</th>
          <th>Ativo</th>
          <th>CRMV</th>
          <th class="text-end">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($vets)): ?>
          <?php foreach ($vets as $v): ?>
            <tr>
              <td><?php echo htmlspecialchars($v['name']); ?></td>
              <td><?php echo htmlspecialchars($v['email']); ?></td>
              <td>
                <?php
                  $uid = (int)$v['id'];
                  // Normaliza vetSpecialties para sempre virar um array de IDs inteiros
                  $rawSpecs = $vetSpecialties[$uid] ?? [];
                  $specIds = [];
                  foreach ($rawSpecs as $item) {
                    if (is_array($item)) {
                      $specIds[] = (int)($item['specialty_id'] ?? $item['id'] ?? 0);
                    } else {
                      $specIds[] = (int)$item;
                    }
                  }
                  $specIds = array_values(array_filter($specIds));

                  // Mapeia nomes das especialidades pelo ID
                  $specNames = [];
                  foreach (($specialties ?? []) as $sp) {
                    if (in_array((int)$sp['id'], $specIds, true)) { $specNames[] = $sp['name']; }
                  }

                  if (empty($specNames)) {
                    echo '—';
                  } else {
                    $firstTwo = array_slice($specNames, 0, 2);
                    $remaining = max(count($specNames) - 2, 0);
                    $display = implode(', ', $firstTwo);
                    if ($remaining > 0) { $display .= ' +' . $remaining; }
                    $full = implode(', ', $specNames);
                    echo '<span title="'.htmlspecialchars($full).'">'.htmlspecialchars($display).'</span>';
                  }
                ?>
              </td>
              <td>
                <?php if (!empty($v['is_active'])): ?>
                  <span class="badge bg-success">Ativo</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inativo</span>
                <?php endif; ?>
              </td>
              <td><?php echo htmlspecialchars($v['crmv'] ?? ''); ?></td>
              <td class="text-end">
                <div class="d-inline-flex align-items-center gap-1">
                  <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#editVeterinarianModal_<?php echo (int)$v['id']; ?>">
                    <i class="fa-regular fa-pen-to-square"></i> Editar
                  </button>
                  <form method="post" action="<?php echo APP_URL; ?>/veterinarians/<?php echo (int)$v['id']; ?>/delete" class="d-inline m-0" onsubmit="return confirm('Excluir este veterinário?');">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                      <i class="fa-regular fa-trash-can"></i> Excluir
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">Nenhum veterinário encontrado.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Modal: Novo Veterinário -->
  <div class="modal fade" id="newVeterinarianModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Novo Veterinário</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="post" action="<?php echo APP_URL; ?>/veterinarians/create">
          <div class="modal-body">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nome</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">E-mail (login)</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Senha (mín. 6 caracteres)</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
              </div>
              <div class="col-md-6 d-flex align-items-end">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="new_is_active" name="is_active" checked>
                  <label class="form-check-label" for="new_is_active">Ativo</label>
                </div>
              </div>

              <div class="col-md-4">
                <label class="form-label">Telefone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">E-mail Profissional</label>
                <input type="email" name="professional_email" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">CRMV</label>
                <input type="text" name="crmv" class="form-control">
              </div>

              <div class="col-md-4">
                <label class="form-label">Vínculo</label>
                <select name="employment_type" class="form-select">
                  <option value="">Selecione</option>
                  <option value="clt">CLT</option>
                  <option value="pj">PJ</option>
                  <option value="estagio">Estágio</option>
                  <option value="terceirizado">Terceirizado</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Admissão</label>
                <input type="date" name="admission_date" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Salário</label>
                <input type="number" step="0.01" name="salary" class="form-control">
              </div>

              <div class="col-md-4">
                <label class="form-label">Carga semanal (horas)</label>
                <input type="number" step="1" name="workload_weekly_hours" class="form-control">
              </div>
              <div class="col-md-8">
                <label class="form-label">Observações</label>
                <textarea name="observations" class="form-control" rows="2"></textarea>
              </div>
              <div class="col-12">
                <label class="form-label">Disponibilidade</label>
                <textarea name="availability_notes" class="form-control" rows="2"></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label">Especialidades</label>
                <select name="specialties[]" class="form-select" multiple size="6">
                  <?php if (!empty($specialties)): ?>
                    <?php foreach ($specialties as $sp): ?>
                      <option value="<?php echo (int)$sp['id']; ?>"><?php echo htmlspecialchars($sp['name']); ?></option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <option disabled>Nenhuma especialidade cadastrada</option>
                  <?php endif; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Unidades</label>
                <select name="units[]" class="form-select" multiple size="6">
                  <?php if (!empty($units)): ?>
                    <?php foreach ($units as $un): ?>
                      <option value="<?php echo (int)$un['id']; ?>"><?php echo htmlspecialchars($un['name']); ?></option>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <option disabled>Nenhuma unidade cadastrada</option>
                  <?php endif; ?>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modais de edição -->
  <?php if (!empty($vets)): ?>
    <?php foreach ($vets as $v): ?>
      <?php $uid = (int)$v['id'];
        $selSpecs = [];
        if (!empty($vetSpecialties[$uid])) {
          $selSpecs = array_map(function($row){ return (int)($row['specialty_id'] ?? $row['id'] ?? 0); }, $vetSpecialties[$uid]);
        }
        $selUnits = [];
        if (!empty($vetUnits[$uid])) {
          $selUnits = array_map(function($row){ return (int)($row['unit_id'] ?? $row['id'] ?? 0); }, $vetUnits[$uid]);
        }
      ?>
      <div class="modal fade" id="editVeterinarianModal_<?php echo $uid; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Editar Veterinário</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?php echo APP_URL; ?>/veterinarians/<?php echo $uid; ?>/edit">
              <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Nome</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($v['name']); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">E-mail (login)</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($v['email']); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Senha (deixe em branco para manter)</label>
                    <input type="password" name="password" class="form-control" minlength="6">
                  </div>
                  <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" value="1" id="edit_is_active_<?php echo $uid; ?>" name="is_active" <?php echo !empty($v['is_active']) ? 'checked' : ''; ?>>
                      <label class="form-check-label" for="edit_is_active_<?php echo $uid; ?>">Ativo</label>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($v['phone'] ?? ''); ?>">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">E-mail Profissional</label>
                    <input type="email" name="professional_email" class="form-control" value="<?php echo htmlspecialchars($v['professional_email'] ?? ''); ?>">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">CRMV</label>
                    <input type="text" name="crmv" class="form-control" value="<?php echo htmlspecialchars($v['crmv'] ?? ''); ?>">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Vínculo</label>
                    <select name="employment_type" class="form-select">
                      <?php $emp = $v['employment_type'] ?? ''; ?>
                      <option value="" <?php echo $emp===''?'selected':''; ?>>Selecione</option>
                      <option value="clt" <?php echo $emp==='clt'?'selected':''; ?>>CLT</option>
                      <option value="pj" <?php echo $emp==='pj'?'selected':''; ?>>PJ</option>
                      <option value="estagio" <?php echo $emp==='estagio'?'selected':''; ?>>Estágio</option>
                      <option value="terceirizado" <?php echo $emp==='terceirizado'?'selected':''; ?>>Terceirizado</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Admissão</label>
                    <input type="date" name="admission_date" class="form-control" value="<?php echo htmlspecialchars($v['admission_date'] ?? ''); ?>">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Salário</label>
                    <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo htmlspecialchars($v['salary'] ?? ''); ?>">
                  </div>

                  <div class="col-md-4">
                    <label class="form-label">Carga semanal (horas)</label>
                    <input type="number" step="1" name="workload_weekly_hours" class="form-control" value="<?php echo htmlspecialchars($v['workload_weekly_hours'] ?? ''); ?>">
                  </div>
                  <div class="col-md-8">
                    <label class="form-label">Observações</label>
                    <textarea name="observations" class="form-control" rows="2"><?php echo htmlspecialchars($v['observations'] ?? ''); ?></textarea>
                  </div>
                  <div class="col-12">
                    <label class="form-label">Disponibilidade</label>
                    <textarea name="availability_notes" class="form-control" rows="2"><?php echo htmlspecialchars($v['availability_notes'] ?? ''); ?></textarea>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Especialidades</label>
                    <select name="specialties[]" class="form-select" multiple size="6">
                      <?php if (!empty($specialties)): ?>
                        <?php foreach ($specialties as $sp): ?>
                          <?php $sid = (int)$sp['id']; $selected = in_array($sid, $selSpecs, true) ? 'selected' : ''; ?>
                          <option value="<?php echo $sid; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($sp['name']); ?></option>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <option disabled>Nenhuma especialidade cadastrada</option>
                      <?php endif; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Unidades</label>
                    <select name="units[]" class="form-select" multiple size="6">
                      <?php if (!empty($units)): ?>
                        <?php foreach ($units as $un): ?>
                          <?php $uidOpt = (int)$un['id']; $selected = in_array($uidOpt, $selUnits, true) ? 'selected' : ''; ?>
                          <option value="<?php echo $uidOpt; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($un['name']); ?></option>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <option disabled>Nenhuma unidade cadastrada</option>
                      <?php endif; ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>