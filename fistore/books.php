<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle = 'Books';

$category = $_GET['category'] ?? '';
$q = trim($_GET['q'] ?? '');

$categories = $pdo->query("SELECT DISTINCT category FROM books ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
if ($q !== '') {
    $sql .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

require_once 'includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Semua Buku</h1>
    <p><?= count($books) ?> buku ditemukan<?= $q !== '' ? ' untuk "' . e($q) . '"' : '' ?></p>
  </div>

  <div class="filter-bar">
    <a href="books.php" class="<?= $category === '' ? 'active' : '' ?>">Semua</a>
    <?php foreach ($categories as $cat): ?>
      <a href="books.php?category=<?= urlencode($cat) ?>" class="<?= $category === $cat ? 'active' : '' ?>"><?= e($cat) ?></a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($books)): ?>
    <div class="empty-state">
      <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <p>Buku tidak ditemukan. Coba kata kunci atau kategori lain.</p>
    </div>
  <?php else: ?>
    <div class="book-grid" style="padding-bottom:60px;">
      <?php foreach ($books as $book): include 'includes/book_card.php'; endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
