<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Dashboard';

$totalBooks = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders")->fetchColumn();
$lowStock = $pdo->query("SELECT * FROM books WHERE stock <= 5 ORDER BY stock ASC LIMIT 5")->fetchAll();
$recentOrders = $pdo->query("SELECT o.*, u.name as customer FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 6")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div class="stat-cards">
  <div class="stat-card"><div class="num"><?= (int)$totalBooks ?></div><div class="label">Total Buku</div></div>
  <div class="stat-card"><div class="num"><?= (int)$totalUsers ?></div><div class="label">Total User</div></div>
  <div class="stat-card"><div class="num"><?= (int)$totalOrders ?></div><div class="label">Total Transaksi</div></div>
  <div class="stat-card"><div class="num"><?= formatRupiah($totalRevenue) ?></div><div class="label">Total Pendapatan</div></div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:24px;align-items:start;">
  <div>
    <h3 style="margin-bottom:14px;">Transaksi Terbaru</h3>
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Invoice</th><th>Pembeli</th><th>Tanggal</th><th>Total</th></tr></thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
            <tr><td colspan="4">Belum ada transaksi.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentOrders as $o): ?>
            <tr>
              <td><?= e($o['invoice_code']) ?></td>
              <td><?= e($o['customer']) ?></td>
              <td><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
              <td><?= formatRupiah($o['total_amount']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <h3 style="margin-bottom:14px;">Stok Menipis</h3>
    <table class="data-table">
      <thead><tr><th>Buku</th><th>Stok</th></tr></thead>
      <tbody>
        <?php if (empty($lowStock)): ?>
          <tr><td colspan="2">Semua stok aman.</td></tr>
        <?php endif; ?>
        <?php foreach ($lowStock as $b): ?>
          <tr><td><?= e($b['title']) ?></td><td style="color:var(--error);font-weight:700;"><?= (int)$b['stock'] ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
