<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Detail Pesanan';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT o.*, u.name as customer, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Pesanan tidak ditemukan.');
    header('Location: orders.php');
    exit;
}

// Verifikasi / tolak bukti pembayaran Transfer Bank & E-Wallet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'], $_POST['decision'])
    && isPrepaidMethod($order['payment_method']) && $order['payment_status'] === 'Menunggu Verifikasi') {
    if ($_POST['decision'] === 'approve') {
        $pdo->prepare("UPDATE orders SET payment_status = 'Terverifikasi', payment_note = NULL, status = IF(status = 'Menunggu Konfirmasi', 'Diproses', status) WHERE id = ?")->execute([$id]);
        flash('success', 'Pembayaran dikonfirmasi. Pesanan masuk ke status "Diproses".');
    } else {
        $note = trim($_POST['note'] ?? '');
        if ($note === '') {
            flash('error', 'Isi alasan penolakan supaya pembeli tahu apa yang perlu diperbaiki.');
            header('Location: order_detail.php?id=' . $id);
            exit;
        }
        $pdo->prepare("UPDATE orders SET payment_status = 'Ditolak', payment_note = ? WHERE id = ?")->execute([mb_substr($note, 0, 255), $id]);
        flash('success', 'Bukti pembayaran ditolak. Pembeli bisa upload ulang.');
    }
    header('Location: order_detail.php?id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['advance_status'])) {
    $next = orderNextStatus($order['status']);
    if ($next && orderCanAdvance($order)) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$next, $id]);
        flash('success', 'Status pesanan diperbarui menjadi "' . $next . '".');
        header('Location: order_detail.php?id=' . $id);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

require_once 'includes/admin_header.php';
?>

<div style="max-width:640px;background:#fff;padding:26px;border-radius:14px;border:1px solid var(--border-color);">
  <div class="receipt-meta">
    <div><span>No. Invoice</span><b><?= e($order['invoice_code']) ?></b></div>
    <div><span>Pembeli</span><b><?= e($order['customer']) ?> (<?= e($order['email']) ?>)</b></div>
    <div><span>Tanggal</span><b><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></b></div>
    <div><span>Alamat</span><b style="text-align:right;max-width:60%;"><?= e($order['buyer_address']) ?></b></div>
    <div><span>Pembayaran</span><b><?= e($order['payment_method']) ?></b></div>
    <?php if ($order['payment_status']): ?>
      <div><span>Status Pembayaran</span><b><span class="badge-status <?= paymentStatusClass($order['payment_status']) ?>"><?= e($order['payment_status']) ?></span></b></div>
    <?php endif; ?>
    <div><span>Status Pesanan</span><b><span class="badge-status <?= orderStatusClass($order['status']) ?>"><?= e($order['status']) ?></span></b></div>
  </div>

  <?php if (isPrepaidMethod($order['payment_method'])): ?>
    <div class="receipt-divider"></div>
    <h3 class="proof-title">Bukti Pembayaran</h3>
    <?php if ($order['payment_proof']): ?>
      <div class="receipt-meta">
        <div><span>Nama Pengirim</span><b><?= e($order['payment_sender']) ?></b></div>
        <div><span>Diunggah</span><b><?= $order['payment_proof_at'] ? date('d M Y, H:i', strtotime($order['payment_proof_at'])) : '-' ?></b></div>
      </div>
      <?= proofViewHtml($order['payment_proof'], '../uploads/proofs/', true) ?>
      <button type="button" class="btn btn-outline btn-sm mt-20" onclick="printProof('../uploads/proofs/<?= e($order['payment_proof']) ?>')">Cetak Bukti Pembayaran</button>
      <p class="form-hint"> Cocokkan nominal <b><?= formatRupiah($order['total_amount']) ?></b> dengan mutasi rekening / e-wallet toko.</p>
      <?php if ($order['payment_status'] === 'Ditolak' && $order['payment_note']): ?>
        <p class="proof-note">Alasan penolakan: <?= e($order['payment_note']) ?></p>
      <?php endif; ?>
      <?php if ($order['payment_status'] === 'Menunggu Verifikasi'): ?>
        <form method="POST" class="proof-actions">
          <input type="hidden" name="verify_payment" value="1">
          <button type="submit" name="decision" value="approve" class="btn btn-primary btn-block">Konfirmasi Pembayaran &amp; Proses Pesanan</button>
          <div class="form-group" style="margin:14px 0 0;">
            <input type="text" name="note" maxlength="255" placeholder="Alasan penolakan (mis. nominal tidak sesuai, foto buram)">
          </div>
          <button type="submit" name="decision" value="reject" class="btn btn-danger btn-block mt-20" onclick="return this.form.note.value.trim() !== '' || (alert('Isi alasan penolakan dulu.'), false);">Tolak Bukti</button>
        </form>
      <?php endif; ?>
    <?php else: ?>
      <p class="form-hint">Pembeli belum mengunggah bukti pembayaran.</p>
    <?php endif; ?>
  <?php endif; ?>
  <div class="receipt-divider"></div>
  <?php foreach ($items as $it): ?>
    <div class="receipt-line">
      <span><?= e($it['title']) ?> &times; <?= (int)$it['quantity'] ?></span>
      <span><?= formatRupiah($it['price'] * $it['quantity']) ?></span>
    </div>
  <?php endforeach; ?>
  <div class="receipt-divider"></div>
  <div class="receipt-total"><span>Total</span><span><?= formatRupiah($order['total_amount']) ?></span></div>

  <?php $next = orderNextStatus($order['status']); ?>
  <?php if ($next && orderCanAdvance($order)): ?>
    <form method="POST" style="margin-top:22px;">
      <button type="submit" name="advance_status" value="1" class="btn btn-primary btn-block">
        Perbarui Status &rarr; <?= e($next) ?>
      </button>
    </form>
  <?php elseif ($next): ?>
    <p class="form-hint mt-20">Pesanan ini baru bisa diproses setelah pembayaran dikonfirmasi lewat bukti pembayaran di atas.</p>
  <?php else: ?>
    <p class="form-hint mt-20">Pesanan ini sudah di status akhir (<?= e($order['status']) ?>), tidak ada status lanjutan.</p>
  <?php endif; ?>

  <a href="orders.php" class="btn btn-outline mt-20">&larr; Kembali ke Daftar Pesanan</a>
</div>

<script>
// Cetak bukti pembayaran lewat iframe tersembunyi; kalau browser menolak, buka di tab baru.
function printProof(url) {
  var f = document.createElement('iframe');
  f.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
  f.src = url;
  f.onload = function () {
    try { f.contentWindow.focus(); f.contentWindow.print(); }
    catch (e) { window.open(url, '_blank'); }
  };
  document.body.appendChild(f);
}
</script>
<?php require_once 'includes/admin_footer.php'; ?>
