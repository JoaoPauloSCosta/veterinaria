<?php require_login(); ?>
<h3>PDV</h3>
<?php if (!empty($flash_error)): ?>
  <div class="alert alert-danger"><?= e($flash_error) ?></div>
<?php endif; ?>
<form method="post" action="<?= e(APP_URL) ?>/sales/checkout" id="posForm">
  <?= csrf_input() ?>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Cliente</label>
      <select name="client_id" class="form-select" required>
        <option value="">Selecione...</option>
        <?php foreach (($clients ?? []) as $cl): ?>
          <option value="<?= e($cl['id']) ?>"><?= e($cl['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3"><label class="form-label">Pagamento</label>
      <select name="method" class="form-select">
        <option value="dinheiro">Dinheiro</option>
        <option value="cartao_credito">Cartão Crédito</option>
        <option value="cartao_debito">Cartão Débito</option>
        <option value="transferencia">Transferência</option>
        <option value="pix">PIX</option>
      </select>
    </div>
  </div>
  <hr>
  <h6>Itens</h6>
  <div id="items"></div>
  <button type="button" class="btn btn-outline-primary" id="addItem">Adicionar Item</button>
  <div class="text-end mt-3"><button class="btn btn-success">Finalizar</button></div>
</form>
<script>
const itemsDiv = document.getElementById('items');
const addBtn = document.getElementById('addItem');
addBtn.addEventListener('click', () => {
  const row = document.createElement('div');
  row.className = 'row g-2 align-items-end mb-2';
  row.innerHTML = `
    <div class=\"col-md-6\">
      <label class=\"form-label\">Produto</label>
      <select name=\"items[][product_id]\" class=\"form-select\" required>
        <option value=\"\">Selecione...</option>
        ${productsOptions()}
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Qtd</label>
      <input name="items[][quantity]" type="number" class="form-control" required value="1">
    </div>
    <div class="col-md-2">
      <button type="button" class="btn btn-outline-danger remove">Remover</button>
    </div>
  `;
  itemsDiv.appendChild(row);
  row.querySelector('.remove').addEventListener('click', () => row.remove());
});

function productsOptions() {
  const data = <?php echo json_encode($products ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  return data.map(p => `<option value="${p.id}">${p.name} ${p.is_service ? '(Serviço)' : ''}</option>`).join('');
}
</script>
