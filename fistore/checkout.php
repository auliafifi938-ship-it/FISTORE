<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();
$pageTitle = 'Checkout';

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$buyNow = $_SESSION['buy_now'] ?? null;

if ($buyNow) {
    // Beli langsung: satu buku, tidak melewati keranjang
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([(int)$buyNow['book_id']]);
    $book = $stmt->fetch();
    if (!$book || $book['stock'] < 1) {
        unset($_SESSION['buy_now']);
        flash('error', 'Buku tidak tersedia atau stok habis.');
        header('Location: books.php');
        exit;
    }
    $qty = min(max(1, (int)$buyNow['quantity']), (int)$book['stock']);
    $items = [array_merge($book, ['cart_id' => 0, 'quantity' => $qty])];
} else {
    $selectedIds = $_SESSION['checkout_cart_ids'] ?? [];
    if (empty($selectedIds)) {
        flash('error', 'Pilih buku yang mau di-checkout dari keranjang dulu.');
        header('Location: cart.php');
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $params = array_merge($selectedIds, [$_SESSION['user_id']]);
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, b.* FROM cart c JOIN books b ON c.book_id = b.id WHERE c.id IN ($placeholders) AND c.user_id = ? ORDER BY c.created_at DESC");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        unset($_SESSION['checkout_cart_ids']);
        flash('error', 'Buku yang dipilih tidak ditemukan di keranjang.');
        header('Location: cart.php');
        exit;
    }
}

$subtotal = 0;
foreach ($items as $it) { $subtotal += $it['price'] * $it['quantity']; }
$shipping = 15000;
$total = $subtotal + $shipping;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

require_once 'includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Checkout</h1>
    <p>Lengkapi data pengiriman dan pembayaran untuk <?= count($items) ?> buku yang kamu pilih</p>
  </div>

  <form method="POST" action="process_checkout.php" enctype="multipart/form-data">
    <div class="checkout-layout">
      <div>
        <div class="checkout-box">
          <h3>Data Penerima</h3>
          <div class="form-group">
            <label>Nama Penerima</label>
            <input type="text" name="buyer_name" required value="<?= e($user['name']) ?>">
          </div>
          <div class="form-group">
            <label>Alamat Pengiriman</label>
            <textarea name="buyer_address" rows="3" required placeholder="Jalan, No Rumah, Kelurahan, Kecamatan, Kota, Kode Pos"></textarea>
          </div>
          <div class="form-group">
            <label>No. HP</label>
            <input type="text" name="buyer_phone" required placeholder="08xxxxxxxxxx">
          </div>
        </div>

        <div class="checkout-box">
          <h3>Metode Pembayaran</h3>
          <label class="pay-option"><input type="radio" name="payment_method" value="Transfer Bank" checked> Transfer Bank</label>
          <div class="pay-detail" id="detail-bank">
            <div class="form-group" style="margin:0;">
              <label>Pilih Bank</label>
              <select name="bank">
                <option value="BCA">BCA</option>
                <option value="BNI">BNI</option>
                <option value="BRI">BRI</option>
                <option value="Mandiri">Mandiri</option>
              </select>
            </div>
            <div class="bank-account" id="bankAccount" data-accounts='<?= e(json_encode(storeBankAccounts())) ?>'>
              <span>Transfer ke rekening <b id="bankName">BCA</b> a.n. <?= e(storeBankHolder()) ?></span>
              <strong id="bankNumber">738 204 5916</strong>
              <span>Nominal: <b><?= formatRupiah($total) ?></b></span>
            </div>
            <?php $proofPrefix = 'bank'; $proofUser = $user['name']; include 'includes/proof_fields.php'; ?>
          </div>

          <label class="pay-option"><input type="radio" name="payment_method" value="Kartu Kredit"> Kartu Kredit</label>
          <div class="pay-detail" id="detail-card" style="display:none;">
            <div class="form-group">
              <label>Nomor Kartu</label>
              <input type="text" name="card_number" id="cardNumber" inputmode="numeric" autocomplete="off" maxlength="23" placeholder="1234 5678 9012 3456">
            </div>
            <div class="form-group">
              <label>Nama di Kartu</label>
              <input type="text" name="card_name" autocomplete="off" placeholder="Sesuai yang tertera di kartu">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <div class="form-group" style="margin:0;">
                <label>Masa Berlaku (MM/YY)</label>
                <input type="text" name="card_expiry" id="cardExpiry" inputmode="numeric" autocomplete="off" maxlength="5" placeholder="08/28">
              </div>
              <div class="form-group" style="margin:0;">
                <label>CVV</label>
                <input type="password" name="card_cvv" inputmode="numeric" autocomplete="off" maxlength="4" placeholder="123">
              </div>
            </div>
            <div class="form-hint">Demi keamanan, hanya 4 digit terakhir kartu yang disimpan di struk. Nomor lengkap dan CVV tidak disimpan.</div>
          </div>

          <label class="pay-option"><input type="radio" name="payment_method" value="E-Wallet"> E-Wallet (OVO / GoPay / Dana)</label>
          <div class="pay-detail" id="detail-ewallet" style="display:none;">
            <div class="form-group" style="margin:0 0 14px;">
              <label>Pilih E-Wallet</label>
              <select name="ewallet" id="ewalletSelect">
                <option value="OVO">OVO</option>
                <option value="GoPay">GoPay</option>
                <option value="Dana">Dana</option>
              </select>
            </div>
            <?php $qrLabel = 'OVO'; include 'includes/qr_frame.php'; ?>
            <p class="form-hint text-center" style="margin:0 0 14px;">Bayar sebesar <b><?= formatRupiah($total) ?></b>, lalu unggah bukti pembayarannya.</p>
            <?php $proofPrefix = 'ewallet'; $proofUser = $user['name']; include 'includes/proof_fields.php'; ?>
          </div>
          
      </div>

      <div class="summary-box">
        <h3>Ringkasan Pesanan</h3>
        <?php foreach ($items as $it): ?>
          <div class="checkout-item-row">
            <span><?= e($it['title']) ?> &times; <?= (int)$it['quantity'] ?></span>
            <span><?= formatRupiah($it['price'] * $it['quantity']) ?></span>
          </div>
        <?php endforeach; ?>
        <div class="summary-row" style="margin-top:14px;"><span>Subtotal</span><span><?= formatRupiah($subtotal) ?></span></div>
        <div class="summary-row"><span>Ongkos Kirim</span><span><?= formatRupiah($shipping) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= formatRupiah($total) ?></span></div>
        <button type="submit" class="btn btn-primary btn-block mt-20">Buat Pesanan</button>
      </div>
    </div>
  </form>
</div>

<script>
(function () {
  var radios = document.querySelectorAll('input[name="payment_method"]');
  var bank = document.getElementById('detail-bank');
  var card = document.getElementById('detail-card');
  var ewallet = document.getElementById('detail-ewallet');
  var ewSelect = document.getElementById('ewalletSelect');
  function sync() {
    var v = document.querySelector('input[name="payment_method"]:checked').value;
    bank.style.display = v === 'Transfer Bank' ? 'block' : 'none';
    card.style.display = v === 'Kartu Kredit' ? 'block' : 'none';
    ewallet.style.display = v === 'E-Wallet' ? 'block' : 'none';
    card.querySelectorAll('input').forEach(function (i) { i.required = (v === 'Kartu Kredit'); });
    bank.querySelectorAll('.proof-field').forEach(function (i) { i.required = (v === 'Transfer Bank'); });
    ewallet.querySelectorAll('.proof-field').forEach(function (i) { i.required = (v === 'E-Wallet'); });
  }
  radios.forEach(function (r) { r.addEventListener('change', sync); });
  var bankSel = document.querySelector('select[name="bank"]');
  var accBox = document.getElementById('bankAccount');
  var accounts = JSON.parse(accBox.dataset.accounts);
  function syncBank() {
    document.getElementById('bankName').textContent = bankSel.value;
    document.getElementById('bankNumber').textContent = accounts[bankSel.value] || '';
  }
  bankSel.addEventListener('change', syncBank);
  syncBank();
  document.querySelectorAll('.proof-file').forEach(function (inp) {
    inp.addEventListener('change', function () {
      var img = inp.parentNode.querySelector('.proof-preview');
      var f = inp.files && inp.files[0];
      if (f && f.type.indexOf('image/') === 0) { img.src = URL.createObjectURL(f); img.style.display = 'block'; } else { img.style.display = 'none'; }
    });
  });
  ewSelect.addEventListener('change', function () {
    ewallet.querySelectorAll('.qr-caption b').forEach(function (b) { b.textContent = ewSelect.value; });
  });
  document.getElementById('cardNumber').addEventListener('input', function () {
    var d = this.value.replace(/\D/g, '').slice(0, 19);
    this.value = d.replace(/(.{4})/g, '$1 ').trim();
  });
  document.getElementById('cardExpiry').addEventListener('input', function () {
    var d = this.value.replace(/\D/g, '').slice(0, 4);
    this.value = d.length > 2 ? d.slice(0, 2) + '/' + d.slice(2) : d;
  });
  sync();
})();
</script>
<?php require_once 'includes/footer.php'; ?>
