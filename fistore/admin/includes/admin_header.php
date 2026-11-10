<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - Admin FISTORE' : 'Admin FISTORE' ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
  body { display: flex; min-height: 100vh; }
  .admin-sidebar {
    width: 230px;
    background: var(--footer-bg);
    color: #fff;
    flex-shrink: 0;
    padding: 24px 0;
  }
  .admin-sidebar .logo { display:block; color:#fff; font-size:22px; font-weight:800; padding: 0 24px 24px; }
  .admin-sidebar .logo span { color: var(--pink); }
  .admin-sidebar nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #e2c3d0;
  }
  .admin-sidebar nav a:hover, .admin-sidebar nav a.active { background: rgba(255,255,255,0.08); color: #fff; }
  .admin-main { flex: 1; background: var(--bg); min-width: 0; }
  .admin-topbar {
    background: #fff;
    padding: 16px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(232,97,126,0.06);
  }
  .admin-content { padding: 26px 28px 60px; }
  .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 28px; }
  .stat-card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border-color); }
  .stat-card .num { font-size: 26px; font-weight: 800; color: var(--pink-darker); }
  .stat-card .label { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
</style>
</head>
<body>

<aside class="admin-sidebar">
  <a href="dashboard.php" class="logo">FI<span>STORE</span></a>
  <nav>
    <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">&#128202; Dashboard</a>
    <a href="books.php" class="<?= in_array($currentPage, ['books.php','add_book.php','edit_book.php']) ? 'active' : '' ?>">&#128218; Kelola Buku</a>
    <a href="orders.php" class="<?= $currentPage === 'orders.php' ? 'active' : '' ?>">&#128230; Pesanan</a>
    <a href="../index.php">&#127968; Lihat Toko</a>
    <a href="logout.php">&#128274; Log out</a>
  </nav>
</aside>

<div class="admin-main">
  <div class="admin-topbar">
    <div><strong><?= isset($pageTitle) ? e($pageTitle) : 'Admin' ?></strong></div>
    <div style="font-size:13px;color:var(--text-muted);">Masuk sebagai <b style="color:var(--text-dark);"><?= e($_SESSION['user_name']) ?></b></div>
  </div>
  <div class="admin-content">
    <?php if ($msg = flash('success')): ?>
      <div class="flash flash-success" style="margin:0 0 18px;"><?= e($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = flash('error')): ?>
      <div class="flash flash-error" style="margin:0 0 18px;"><?= e($msg) ?></div>
    <?php endif; ?>
