<?php require_login(); require_role(['admin']); ?>
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Cadastrar Usuário</h5>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= e(APP_URL) ?>/users/new">
          <?= csrf_input() ?>
          <div class="mb-3">
            <label class="form-label">Nome</label>
            <input name="name" class="form-control" required value="<?= e($name ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required value="<?= e($email ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Perfil</label>
            <select name="role" class="form-select" required>
              <?php $r = $role ?? 'veterinario'; ?>
              <option value="veterinario" <?= $r==='veterinario'?'selected':'' ?>>Veterinário</option>
              <option value="recepcao" <?= $r==='recepcao'?'selected':'' ?>>Recepção</option>
              <option value="financeiro" <?= $r==='financeiro'?'selected':'' ?>>Financeiro</option>
              <option value="admin" <?= $r==='admin'?'selected':'' ?>>Administrador</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
            <div class="form-text">Mínimo 6 caracteres.</div>
          </div>
          <div class="form-check mb-3">
            <?php $ia = (int)($isActive ?? 1); ?>
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" <?= $ia? 'checked':'' ?>>
            <label class="form-check-label" for="is_active">Ativo</label>
          </div>
          <div class="text-end">
            <button class="btn btn-primary btn-top"><i class="fa-solid fa-floppy-disk"></i> Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
