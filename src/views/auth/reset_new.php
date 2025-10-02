<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow-sm mt-4">
      <div class="card-body">
        <h5 class="card-title">Definir nova senha</h5>
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= e(APP_URL) ?>/password/new">
          <?= csrf_input() ?>
          <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
          <div class="mb-3">
            <label class="form-label">Nova senha</label>
            <input type="password" name="password" class="form-control" required minlength="6">
          </div>
          <div class="text-end"><button class="btn btn-primary">Salvar</button></div>
        </form>
      </div>
    </div>
  </div>
</div>
