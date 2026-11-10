<?php
// Halaman yang meng-include file ini WAJIB sudah require config.php & functions.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? e($pageTitle) . ' - FISTORE' : 'FISTORE - Toko Buku Online' ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body<?= !empty($hideNav) ? ' class="auth-only"' : '' ?>>

<?php if (empty($hideNav)): ?>
<header class="site-header">
  <div class="header-top container">
    <a href="index.php" class="logo">FI<span>STORE</span></a>

    <form action="books.php" method="GET" class="search-form">
      <input type="text" name="q" placeholder="Cari buku..." value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>">
      <button type="submit" aria-label="Cari">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      </button>
    </form>

    <div class="header-actions">
      <a href="cart.php" class="icon-link" title="Keranjang">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <?php if (isLoggedIn()): $cc = cartCount($pdo); if ($cc > 0): ?>
          <span class="badge"><?= $cc ?></span>
        <?php endif; endif; ?>
      </a>

      <?php if (isLoggedIn()): ?>
        <div class="user-menu">
          <a href="#" class="icon-link user-toggle" title="Akun">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span class="user-name"><?= e($_SESSION['user_name']) ?></span>
          </a>
          <div class="user-dropdown">
            <a href="orders.php">Pesanan Saya</a>
            <a href="logout.php">Log out</a>
          </div>
        </div>
      <?php else: ?>
        <a href="login.php" class="icon-link" title="Login">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Log in</span>
        </a>
      <?php endif; ?>
    </div>
  </div>

  <nav class="main-nav">
    <div class="container">
      <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Home</a>
      <a href="books.php" class="<?= $currentPage === 'books.php' || $currentPage === 'book_detail.php' ? 'active' : '' ?>">Books</a>
      <a href="wishlist.php" class="<?= $currentPage === 'wishlist.php' ? 'active' : '' ?>">Wishlist</a>
    </div>
  </nav>
</header>
<?php endif; ?>

<?php if ($msg = flash('success')): ?>
  <div class="flash flash-success container"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="flash flash-error container"><?= e($msg) ?></div>
<?php endif; ?>
