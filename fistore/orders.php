<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();
$pageTitle = 'Pesanan Saya';

if (isAdmin()) {
    header('Location: admin/orders.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Pesanan Saya</h1>
    <p><?= count($orders) ?> transaksi</p>
  </div>

  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <p>Kamu belum pernah bertransaksi.</p>
      <a href="books.php" class="btn btn-primary mt-20">Mulai Belanja</a>
    </div>
  <?php else: ?>
    <div style="padding-bottom:60px;overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr><th>Invoice</th><th>Tanggal</th><th>Pembayaran</th><th>Status</th><th>Total</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td><?= e($o['invoice_code']) ?></td>
            <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
            <td>
              <?= e($o['payment_method']) ?>
              <?php if ($o['payment_status']): ?>
                <br><span class="badge-status <?= paymentStatusClass($o['payment_status']) ?>"><?= e($o['payment_status']) ?></span>
              <?php endif; ?>
            </td>
            <td><span class="badge-status <?= orderStatusClass($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td><?= formatRupiah($o['total_amount']) ?></td>
            <td><a href="receipt.php?order_id=<?= (int)$o['id'] ?>" class="btn btn-outline btn-sm"><?= orderCanUploadProof($o) ? 'Upload Bukti' : 'Lihat Struk' ?></a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
