<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Acesso ao Sistema</h5>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= e(APP_URL) ?>/login" novalidate>
          <?= csrf_input() ?>
          <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>
          <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Lembrar-me</label>
          </div>
          <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
        <div class="mt-3">
          <a href="#" class="link-secondary" data-bs-toggle="modal" data-bs-target="#resetModal">Esqueci minha senha</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal de reset de senha -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Recuperar senha</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" action="<?= e(APP_URL) ?>/password/reset">
          <?= csrf_input() ?>
          <div class="mb-3">
            <label for="reset_email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="reset_email" name="email" required>
          </div>
          <button type="submit" class="btn btn-primary">Enviar link</button>
        </form>
      </div>
    </div>
  </div>
</div>
