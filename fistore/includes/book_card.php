<?php
/**
 * Partial: kartu buku.
 * Membutuhkan variabel $book (array baris tabel books) dan $pdo.
 */
$inWish = isInWishlist($pdo, $book['id']);
?>
<div class="book-card">
  <a href="book_detail.php?id=<?= (int)$book['id'] ?>">
    <?php if (!empty($book['cover_image'])): ?>
      <div class="book-cover has-image" style="background:<?= e($book['cover_color']) ?>;">
        <span class="cat-tag"><?= e($book['category']) ?></span>
        <img class="cover-img" src="uploads/covers/<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
      </div>
    <?php else: ?>
      <div class="book-cover" style="background:<?= e($book['cover_color']) ?>;">
        <span class="cat-tag"><?= e($book['category']) ?></span>
        <svg class="book-icon" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        <span style="position:relative;"><?= e($book['title']) ?></span>
      </div>
    <?php endif; ?>
  </a>
  <?php if (isLoggedIn() && !isAdmin()): ?>
    <form action="wishlist_action.php" method="POST" style="position:absolute;top:0;right:0;">
      <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
      <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI']) ?>">
      <button type="submit" class="wishlist-btn <?= $inWish ? 'active' : '' ?>" title="Wishlist">
        <?= $inWish ? '&#9829;' : '&#9825;' ?>
      </button>
    </form>
  <?php endif; ?>
  <div class="book-info">
    <a href="book_detail.php?id=<?= (int)$book['id'] ?>">
      <div class="b-title"><?= e($book['title']) ?></div>
    </a>
    <div class="b-author"><?= e($book['author']) ?></div>
    <div class="b-price"><?= formatRupiah($book['price']) ?></div>
  </div>
</div>
