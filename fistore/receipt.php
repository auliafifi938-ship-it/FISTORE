<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();
$pageTitle = 'Bukti Pembayaran';

$orderId = (int)($_GET['order_id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Struk tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $it) { $subtotal += $it['price'] * $it['quantity']; }
$shipping = $order['total_amount'] - $subtotal;

require_once 'includes/header.php';
?>
<div class="container">
  <div class="receipt-wrap">
    <div class="receipt-card">
      <div class="receipt-head">
        <div class="check-circle">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2>Pesanan Berhasil Dibuat</h2>
        <p>Terima kasih sudah berbelanja di FISTORE</p>
      </div>
      <div class="receipt-body">
        <div class="receipt-meta">
          <div><span>No. Invoice</span><b><?= e($order['invoice_code']) ?></b></div>
          <div><span>Tanggal</span><b><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></b></div>
          <div><span>Penerima</span><b><?= e($order['buyer_name']) ?></b></div>
          <div><span>Pembayaran</span><b><?= e($order['payment_method']) ?></b></div>
          <?php if (strpos($order['payment_method'], 'Transfer Bank') === 0): ?>
            <?php $bk = trim(substr($order['payment_method'], strlen('Transfer Bank - '))); $accs = storeBankAccounts(); ?>
            <?php if (isset($accs[$bk])): ?>
              <div><span>Rekening Tujuan</span><b><?= e($bk) ?> <?= e($accs[$bk]) ?> a.n. <?= e(storeBankHolder()) ?></b></div>
            <?php endif; ?>
          <?php endif; ?>
          <?php if ($order['payment_status']): ?>
            <div><span>Status Pembayaran</span><b><span class="badge-status <?= paymentStatusClass($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></b></div>
          <?php endif; ?>
          <div><span>Status Pesanan</span><b><span class="badge-status <?= orderStatusClass($order['status']) ?>"><?= e($order['status']) ?></span></b></div>
        </div>

        <div class="receipt-divider"></div>

        <?php foreach ($items as $it): ?>
          <div class="receipt-line">
            <span><?= e($it['title']) ?> &times; <?= (int)$it['quantity'] ?></span>
            <span><?= formatRupiah($it['price'] * $it['quantity']) ?></span>
          </div>
        <?php endforeach; ?>

        <div class="receipt-divider"></div>

        <div class="receipt-line"><span>Subtotal</span><span><?= formatRupiah($subtotal) ?></span></div>
        <div class="receipt-line"><span>Ongkos Kirim</span><span><?= formatRupiah($shipping) ?></span></div>
        <div class="receipt-total"><span>Total</span><span><?= formatRupiah($order['total_amount']) ?></span></div>

        <?php if (isPrepaidMethod($order['payment_method'])): ?>
          <?php require 'includes/payment_proof_section.php'; ?>
        <?php endif; ?>

        <div class="receipt-actions no-print">
          <button onclick="window.print()" class="btn btn-outline btn-block">Cetak Struk</button>
          <a href="orders.php" class="btn btn-primary btn-block">Pesanan Saya</a>
        </div>
        <p class="receipt-footer-note">Simpan struk ini sebagai bukti transaksi kamu. Status pesanan akan diperbarui oleh admin toko — cek lewat "Pesanan Saya".</p>
      </div>
    </div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
