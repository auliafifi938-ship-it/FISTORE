  <?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle = 'Home';

$newBooks = $pdo->query("SELECT * FROM books WHERE is_new = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
$bestSellers = $pdo->query("SELECT * FROM books WHERE is_bestseller = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();
$trending = $pdo->query("SELECT * FROM books WHERE is_trending = 1 ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Buku unggulan untuk banner hero
$featured = $pdo->query("SELECT * FROM books WHERE is_bestseller = 1 ORDER BY id ASC LIMIT 1")->fetch();

require_once 'includes/header.php';
?>

<div class="container">
  <section class="hero">
    <div class="container" style="padding-left:0;padding-right:0;">
      <div class="hero-text">
        <span class="hero-tag">DISKON UNTUK PENGGUNA BARU</span>
        <h1>Diskon <span class="accent">35%</span><br>Untuk Member Baru</h1>
        <a href="books.php" class="hero-cta">Belanja Sekarang</a>
      </div>
      <?php if ($featured): ?>
      <div class="hero-book">
        <?php if (!empty($featured['cover_image'])): ?>
          <div class="book-cover has-image" style="background:<?= e($featured['cover_color']) ?>;width:120px;flex-shrink:0;border-radius:10px;">
            <img class="cover-img" src="uploads/covers/<?= e($featured['cover_image']) ?>" alt="<?= e($featured['title']) ?>">
          </div>
        <?php else: ?>
          <div class="book-cover" style="background:<?= e($featured['cover_color']) ?>;width:120px;flex-shrink:0;border-radius:10px;">
            <span style="position:relative;"><?= e($featured['title']) ?></span>
          </div>
        <?php endif; ?>
        <div class="hero-book-info">
          <h3><?= e($featured['category']) ?></h3>
          <h2><?= e($featured['title']) ?></h2>
          <p>oleh <?= e($featured['author']) ?></p>
          <p style="margin-top:8px;font-weight:800;color:var(--pink-darker);font-size:18px;"><?= formatRupiah($featured['price']) ?></p>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2>What's New</h2>
      <a href="books.php">Lihat semua &rarr;</a>
    </div>
    <div class="book-grid">
      <?php foreach ($newBooks as $book): include 'includes/book_card.php'; endforeach; ?>
      <?php if (empty($newBooks)): ?><p class="form-hint">Belum ada buku baru.</p><?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2>Best Seller</h2>
      <a href="books.php">Lihat semua &rarr;</a>
    </div>
    <div class="book-grid">
      <?php foreach ($bestSellers as $book): include 'includes/book_card.php'; endforeach; ?>
      <?php if (empty($bestSellers)): ?><p class="form-hint">Belum ada best seller.</p><?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="section-header">
      <h2>Trending Now</h2>
      <a href="books.php">Lihat semua &rarr;</a>
    </div>
    <div class="book-grid">
      <?php foreach ($trending as $book): include 'includes/book_card.php'; endforeach; ?>
      <?php if (empty($trending)): ?><p class="form-hint">Belum ada buku trending.</p><?php endif; ?>
    </div>
  </section>
</div>

<?php require_once 'includes/footer.php'; ?>
