<?php require_login(); require_role(['admin']); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= e(APP_URL) ?>/dashboard">Dashboard</a></li>
      <li class="breadcrumb-item active" aria-current="page">Configurações do Sistema</li>
    </ol>
  </nav>
  <form method="post" action="<?= e(APP_URL) ?>/settings/reset" onsubmit="return confirm('Restaurar padrões? Esta ação não pode ser desfeita.');">
    <?= csrf_input() ?>
    <button class="btn btn-outline-danger"><i class="fa-solid fa-rotate-left"></i> Restaurar Padrões</button>
  </form>
</div>
<h3 class="mb-3">Configurações do Sistema</h3>

<div class="row">
  <div class="col-lg-3 mb-3">
    <div class="list-group" id="settings-tabs" role="tablist">
      <a class="list-group-item list-group-item-action active" data-bs-toggle="list" href="#tab-users" role="tab"><i class="fa-regular fa-user"></i> Usuários & Permissões</a>
      <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-general" role="tab"><i class="fa-solid fa-gear"></i> Gerais</a>
      <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-integrations" role="tab"><i class="fa-solid fa-plug"></i> Integrações</a>
      <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-stock" role="tab"><i class="fa-solid fa-boxes-stacked"></i> Estoque & Financeiro</a>
      <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-security" role="tab"><i class="fa-solid fa-lock"></i> Segurança</a>
      <a class="list-group-item list-group-item-action" data-bs-toggle="list" href="#tab-notify" role="tab"><i class="fa-regular fa-bell"></i> Notificações</a>
    </div>
  </div>
  <div class="col-lg-9">
    <form method="post" action="<?= e(APP_URL) ?>/settings/save" enctype="multipart/form-data">
      <?= csrf_input() ?>
      <div class="tab-content">
        <!-- Usuários & Permissões -->
        <div class="tab-pane fade show active" id="tab-users" role="tabpanel">
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Gerenciamento de Usuários</h5>
            <p class="text-muted">Para cadastrar novos usuários utilize o atalho no Dashboard ou vá para <a href="<?= e(APP_URL) ?>/users/new">Cadastrar Usuário</a>.</p>
            <ul class="small text-muted mb-0">
              <li>Perfis: Administrador, Veterinário, Recepcionista, Financeiro.</li>
              <li>Todas as alterações são registradas em log.</li>
            </ul>
          </div></div>
        </div>

        <!-- Gerais -->
        <div class="tab-pane fade" id="tab-general" role="tabpanel">
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Dados da Clínica</h5>
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Nome da clínica</label><input class="form-control" name="clinic_name" value="<?= e($settings['clinic_name'] ?? '') ?>"></div>
              <div class="col-md-6"><label class="form-label">E-mail oficial</label><input class="form-control" name="clinic_email" value="<?= e($settings['clinic_email'] ?? '') ?>"></div>
              <div class="col-md-8"><label class="form-label">Endereço</label><input class="form-control" name="clinic_address" value="<?= e($settings['clinic_address'] ?? '') ?>"></div>
              <div class="col-md-4"><label class="form-label">Telefone</label><input class="form-control" name="clinic_phone" value="<?= e($settings['clinic_phone'] ?? '') ?>"></div>
              <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control"></div>
              <div class="col-md-3"><label class="form-label">Idioma</label><input class="form-control" name="locale" value="<?= e($settings['locale'] ?? 'pt_BR') ?>"></div>
              <div class="col-md-3"><label class="form-label">Fuso horário</label><input class="form-control" name="timezone" value="<?= e($settings['timezone'] ?? 'America/Sao_Paulo') ?>"></div>
            </div>
          </div></div>
        </div>

        <!-- Integrações -->
        <div class="tab-pane fade" id="tab-integrations" role="tabpanel">
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">SMTP (E-mail)</h5>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Host</label><input class="form-control" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>"></div>
              <div class="col-md-2"><label class="form-label">Porta</label><input class="form-control" name="smtp_port" value="<?= e($settings['smtp_port'] ?? '') ?>"></div>
              <div class="col-md-3"><label class="form-label">Usuário</label><input class="form-control" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>"></div>
              <div class="col-md-3"><label class="form-label">Senha</label><input type="password" class="form-control" name="smtp_pass" value="<?= e($settings['smtp_pass'] ?? '') ?>"></div>
              <div class="col-md-3"><label class="form-label">Criptografia</label><input class="form-control" name="smtp_secure" value="<?= e($settings['smtp_secure'] ?? 'tls') ?>"></div>
              <div class="col-md-5"><label class="form-label">De (e-mail)</label><input class="form-control" name="smtp_from" value="<?= e($settings['smtp_from'] ?? '') ?>"></div>
              <div class="col-md-4"><label class="form-label">De (nome)</label><input class="form-control" name="smtp_from_name" value="<?= e($settings['smtp_from_name'] ?? '') ?>"></div>
            </div>
          </div></div>

          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Automação</h5>
            <div class="row g-3">
              <div class="col-md-8"><label class="form-label">Webhook URL</label><input class="form-control" name="webhook_url" value="<?= e($settings['webhook_url'] ?? '') ?>" placeholder="https://n8n..." ></div>
              <div class="col-md-4"><label class="form-label">API Key</label><input class="form-control" name="api_key" value="<?= e($settings['api_key'] ?? '') ?>"></div>
            </div>
          </div></div>
        </div>

        <!-- Estoque & Financeiro -->
        <div class="tab-pane fade" id="tab-stock" role="tabpanel">
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Estoque</h5>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Alerta de estoque mínimo</label><input type="number" class="form-control" name="min_stock_alert" value="<?= e($settings['min_stock_alert'] ?? '0') ?>"></div>
            </div>
          </div></div>
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Financeiro</h5>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Período padrão relatórios</label><input class="form-control" name="finance_report_period" value="<?= e($settings['finance_report_period'] ?? 'mensal') ?>"></div>
              <div class="col-md-2"><label class="form-label">Moeda</label><input class="form-control" name="currency" value="<?= e($settings['currency'] ?? 'BRL') ?>"></div>
              <div class="col-md-2"><label class="form-label">Imposto (%)</label><input type="number" step="0.01" class="form-control" name="tax_rate" value="<?= e($settings['tax_rate'] ?? '0') ?>"></div>
              <div class="col-md-4"><label class="form-label">Formas de pagamento</label>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="pay_card" name="payments_card" <?= !empty($settings['payments_card'])?'checked':'' ?>><label class="form-check-label" for="pay_card">Cartão</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="pay_boleto" name="payments_boleto" <?= !empty($settings['payments_boleto'])?'checked':'' ?>><label class="form-check-label" for="pay_boleto">Boleto</label></div>
                <div class="form-check"><input class="form-check-input" type="checkbox" id="pay_pix" name="payments_pix" <?= !empty($settings['payments_pix'])?'checked':'' ?>><label class="form-check-label" for="pay_pix">PIX</label></div>
              </div>
            </div>
          </div></div>
        </div>

        <!-- Segurança -->
        <div class="tab-pane fade" id="tab-security" role="tabpanel">
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Senha & Política</h5>
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Alterar senha do admin (opcional)</label><input type="password" class="form-control" name="admin_new_password"></div>
              <div class="col-md-4"><label class="form-label">Mínimo de caracteres</label><input type="number" class="form-control" name="password_min_length" value="<?= e($settings['password_min_length'] ?? '8') ?>"></div>
              <div class="col-md-4"><label class="form-label">Expiração (dias)</label><input type="number" class="form-control" name="password_exp_days" value="<?= e($settings['password_exp_days'] ?? '0') ?>"></div>
            </div>
          </div></div>
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Auditoria</h5>
            <p class="text-muted mb-2">Últimas alterações no sistema:</p>
            <div class="small text-muted">Ver seção de logs (futuro).</div>
          </div></div>
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Backup</h5>
            <p class="small text-muted">Exportação do banco via rotina externa (ver checklist_deploy.md).</p>
          </div></div>
        </div>

        <!-- Notificações -->
        <div class="tab-pane fade" id="tab-notify" role="tabpanel">
          <div class="card mb-3"><div class="card-body">
            <h5 class="card-title">Preferências</h5>
            <div class="row g-3">
              <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" id="notify_email" name="notify_email" <?= !empty($settings['notify_email'])?'checked':'' ?>><label class="form-check-label" for="notify_email">E-mail</label></div></div>
              <div class="col-md-3"><div class="form-check"><input class="form-check-input" type="checkbox" id="notify_system" name="notify_system" <?= !empty($settings['notify_system'])?'checked':'' ?>><label class="form-check-label" for="notify_system">Sistema</label></div></div>
            </div>
            <div class="row g-3 mt-2">
              <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" id="notify_appointment" name="notify_appointment" <?= !empty($settings['notify_appointment'])?'checked':'' ?>><label class="form-check-label" for="notify_appointment">Consultas</label></div></div>
              <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" id="notify_vaccine" name="notify_vaccine" <?= !empty($settings['notify_vaccine'])?'checked':'' ?>><label class="form-check-label" for="notify_vaccine">Vacinas</label></div></div>
              <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" id="notify_due" name="notify_due" <?= !empty($settings['notify_due'])?'checked':'' ?>><label class="form-check-label" for="notify_due">Vencimentos</label></div></div>
            </div>
          </div></div>
        </div>
      </div>
      <div class="text-end mt-3">
        <button class="btn btn-primary btn-top"><i class="fa-solid fa-floppy-disk"></i> Salvar Configurações</button>
      </div>
    </form>
  </div>
</div>
