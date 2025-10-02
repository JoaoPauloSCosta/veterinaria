<?php require_login(); ?>
<div class="p-4 bg-white border rounded">
  <h4 class="mb-3">Recibo de Venda #<?= e($invoice['id']) ?></h4>
  <p><strong>Status:</strong> <?= e($invoice['status']) ?> | <strong>Total:</strong> R$ <?= number_format((float)$invoice['total'], 2, ',', '.') ?></p>
  <table class="table table-sm">
    <thead><tr><th>Produto</th><th>Qtd</th><th>Unitário</th><th>Subtotal</th></tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
        <tr>
          <td><?= e($it['name']) ?></td>
          <td><?= e($it['quantity']) ?></td>
          <td>R$ <?= number_format((float)$it['unit_price'], 2, ',', '.') ?></td>
          <td>R$ <?= number_format((float)$it['subtotal'], 2, ',', '.') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="text-end">
    <button class="btn btn-outline-secondary" onclick="window.print()">Imprimir</button>
  </div>
</div>
