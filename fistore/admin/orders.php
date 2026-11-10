<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Pesanan';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['advance_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $stmt = $pdo->prepare("SELECT status, payment_method, payment_status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $row = $stmt->fetch();
    if ($row) {
        $next = orderNextStatus($row['status']);
        if ($next && orderCanAdvance($row)) {
            $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$next, $orderId]);
            flash('success', 'Status pesanan diperbarui menjadi "' . $next . '".');
        }
    }
    header('Location: orders.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT o.*, u.name as customer, u.email FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];
if ($statusFilter !== '') {
    $sql .= " WHERE o.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="filter-bar">
  <a href="orders.php" class="<?= $statusFilter === '' ? 'active' : '' ?>">Semua</a>
  <?php foreach (orderStatusList() as $s): ?>
    <a href="orders.php?status=<?= urlencode($s) ?>" class="<?= $statusFilter === $s ? 'active' : '' ?>"><?= e($s) ?></a>
  <?php endforeach; ?>
</div>

<div style="overflow-x:auto;">
<table class="data-table">
  <thead>
    <tr><th>Invoice</th><th>Pembeli</th><th>Email</th><th>Tanggal</th><th>Pembayaran</th><th>Status</th><th>Total</th><th></th></tr>
  </thead>
  <tbody>
    <?php if (empty($orders)): ?>
      <tr><td colspan="8">Belum ada transaksi.</td></tr>
    <?php endif; ?>
    <?php foreach ($orders as $o): ?>
      <tr>
        <td><?= e($o['invoice_code']) ?></td>
        <td><?= e($o['customer']) ?></td>
        <td><?= e($o['email']) ?></td>
        <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
        <td>
          <?= e($o['payment_method']) ?>
          <?php if ($o['payment_status']): ?>
            <br><span class="badge-status <?= paymentStatusClass($o['payment_status']) ?>"><?= e($o['payment_status']) ?></span>
          <?php endif; ?>
        </td>
        <td><span class="badge-status <?= orderStatusClass($o['status']) ?>"><?= e($o['status']) ?></span></td>
        <td><?= formatRupiah($o['total_amount']) ?></td>
        <td style="white-space:nowrap;">
          <a href="order_detail.php?id=<?= (int)$o['id'] ?>" class="btn btn-outline btn-sm">Detail</a>
          <?php $next = orderNextStatus($o['status']); ?>
          <?php if ($next && orderCanAdvance($o)): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
              <button type="submit" name="advance_status" value="1" class="btn btn-primary btn-sm" title="Ubah ke: <?= e($next) ?>">Perbarui Status</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
