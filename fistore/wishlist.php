<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();
$pageTitle = 'Wishlist';

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT b.* FROM wishlist w JOIN books b ON w.book_id = b.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$books = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Wishlist Saya</h1>
    <p><?= count($books) ?> buku disimpan</p>
  </div>

  <?php if (empty($books)): ?>
    <div class="empty-state">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
      <p>Wishlist kamu masih kosong.</p>
      <a href="books.php" class="btn btn-primary mt-20">Jelajahi Buku</a>
    </div>
  <?php else: ?>
    <div class="book-grid" style="padding-bottom:60px;">
      <?php foreach ($books as $book): include 'includes/book_card.php'; endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
