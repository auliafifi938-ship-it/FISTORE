<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();
$pageTitle = 'Keranjang';

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, b.* FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $it) {
    $subtotal += $it['price'] * $it['quantity'];
}
$shipping = $subtotal > 0 ? 15000 : 0;
$total = $subtotal + $shipping;

require_once 'includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Keranjang Belanja</h1>
    <p><?= count($items) ?> item di keranjang</p>
  </div>

  <?php if (empty($items)): ?>
    <div class="empty-state">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <p>Keranjang kamu masih kosong.</p>
      <a href="books.php" class="btn btn-primary mt-20">Mulai Belanja</a>
    </div>
  <?php else: ?>
    <form method="POST" action="checkout_select.php" id="cartForm">
      <input type="hidden" name="cart_id" id="cartIdField" value="">

      <div class="cart-layout">
        <div>
          <label class="select-all-row">
            <input type="checkbox" id="selectAll" checked>
            <span>Pilih semua buku</span>
          </label>

          <?php foreach ($items as $it): ?>
            <div class="cart-item">
              <input type="checkbox" name="selected[]" value="<?= (int)$it['cart_id'] ?>" class="item-check" data-total="<?= (int)($it['price'] * $it['quantity']) ?>" checked>

              <?php if (!empty($it['cover_image'])): ?>
                <div class="mini-cover has-image" style="background:<?= e($it['cover_color']) ?>;">
                  <img src="uploads/covers/<?= e($it['cover_image']) ?>" alt="<?= e($it['title']) ?>">
                </div>
              <?php else: ?>
                <div class="mini-cover" style="background:<?= e($it['cover_color']) ?>;"><?= e(mb_strimwidth($it['title'], 0, 24, '...')) ?></div>
              <?php endif; ?>

              <div class="ci-main">
                <div class="ci-title"><?= e($it['title']) ?></div>
                <div class="ci-price"><?= formatRupiah($it['price']) ?> / buku</div>
              </div>

              <span class="qty-inline">
                <button type="submit" formaction="cart_action.php" formnovalidate name="action" value="decrease" onclick="document.getElementById('cartIdField').value=<?= (int)$it['cart_id'] ?>">-</button>
                <span><?= (int)$it['quantity'] ?></span>
                <button type="submit" formaction="cart_action.php" formnovalidate name="action" value="increase" onclick="document.getElementById('cartIdField').value=<?= (int)$it['cart_id'] ?>" <?= $it['quantity'] >= $it['stock'] ? 'disabled' : '' ?>>+</button>
              </span>

              <div class="ci-price-line"><?= formatRupiah($it['price'] * $it['quantity']) ?></div>

              <button type="submit" formaction="cart_action.php" formnovalidate name="action" value="remove" class="remove-link" style="background:none;border:none;" onclick="document.getElementById('cartIdField').value=<?= (int)$it['cart_id'] ?>">Hapus</button>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="summary-box">
          <h3>Ringkasan Belanja</h3>
          <div class="summary-row"><span>Subtotal</span><span id="subtotalVal"><?= formatRupiah($subtotal) ?></span></div>
          <div class="summary-row"><span>Ongkos Kirim</span><span id="shippingVal"><?= formatRupiah($shipping) ?></span></div>
          <div class="summary-row total"><span>Total</span><span id="totalVal"><?= formatRupiah($total) ?></span></div>
          <button type="submit" formaction="checkout_select.php" id="checkoutBtn" class="btn btn-primary btn-block mt-20">Checkout Buku Terpilih</button>
          <p class="form-hint mt-20">Centang buku yang mau kamu checkout. Buku yang tidak dicentang akan tetap ada di keranjang.</p>
        </div>
      </div>
    </form>
  <?php endif; ?>
</div>

<script>
function formatRupiahJS(n) {
  return 'Rp' + n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
function recalcCart() {
  var subtotal = 0, anyChecked = false;
  document.querySelectorAll('.item-check:checked').forEach(function (cb) {
    subtotal += parseInt(cb.dataset.total, 10);
    anyChecked = true;
  });
  var shipping = anyChecked ? 15000 : 0;
  var total = subtotal + shipping;
  document.getElementById('subtotalVal').textContent = formatRupiahJS(subtotal);
  document.getElementById('shippingVal').textContent = formatRupiahJS(shipping);
  document.getElementById('totalVal').textContent = formatRupiahJS(total);
  document.getElementById('checkoutBtn').disabled = !anyChecked;
}
document.querySelectorAll('.item-check').forEach(function (cb) {
  cb.addEventListener('change', function () {
    var all = document.querySelectorAll('.item-check');
    var checked = document.querySelectorAll('.item-check:checked');
    document.getElementById('selectAll').checked = all.length === checked.length;
    recalcCart();
  });
});
document.getElementById('selectAll').addEventListener('change', function () {
  document.querySelectorAll('.item-check').forEach(function (cb) { cb.checked = document.getElementById('selectAll').checked; });
  recalcCart();
});
recalcCart();
</script>

<?php require_once 'includes/footer.php'; ?>
