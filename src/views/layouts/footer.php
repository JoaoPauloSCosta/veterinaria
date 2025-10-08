</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Expor APP_URL ao JavaScript para construir URLs absolutas corretas
  window.APP_URL = '<?= e(APP_URL) ?>';
</script>
<script src="<?= e(APP_URL) ?>/assets/js/notifications.js"></script>
<script>
  // Recarrega suavemente a página quando QUALQUER modal é fechado
  (function () {
    var reloadScheduled = false;
    document.addEventListener('hidden.bs.modal', function (event) {
      // Permitir exceção opcional via atributo data-no-refresh="true" em modais específicos
      var modalEl = event.target;
      if (modalEl && modalEl.getAttribute && modalEl.getAttribute('data-no-refresh') === 'true') {
        return;
      }
      if (!reloadScheduled) {
        reloadScheduled = true;
        try {
          // Feedback visual suave antes do reload
          document.body.style.transition = 'opacity 180ms ease';
          document.body.style.opacity = '0.6';
        } catch (e) {}
        setTimeout(function(){ window.location.reload(); }, 220);
      }
    });
  })();
</script>
</body>
</html>
