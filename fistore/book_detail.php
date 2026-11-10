 <?php
require_once 'config.php';
require_once 'includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    flash('error', 'Buku tidak ditemukan.');
    header('Location: books.php');
    exit;
}
$pageTitle = $book['title'];

// Checkout langsung (beli sekarang) tanpa memasukkan ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_now'])) {
    requireLogin();
    if (isAdmin()) {
        flash('error', 'Akun admin tidak bisa berbelanja.');
        header('Location: book_detail.php?id=' . $book['id']);
        exit;
    }
    if ($book['stock'] < 1) {
        flash('error', 'Stok buku ini sedang habis.');
        header('Location: book_detail.php?id=' . $book['id']);
        exit;
    }
    $qty = min(max(1, (int)($_POST['quantity'] ?? 1)), (int)$book['stock']);
    $_SESSION['buy_now'] = ['book_id' => (int)$book['id'], 'quantity' => $qty];
    unset($_SESSION['checkout_cart_ids']); // pastikan tidak bentrok dengan checkout dari keranjang
    header('Location: checkout.php');
    exit;
}

// Tambah ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    requireLogin();
    if (isAdmin()) {
        flash('error', 'Akun admin tidak bisa berbelanja.');
    } else {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$_SESSION['user_id'], $book['id']]);
        $existing = $stmt->fetch();
        if ($existing) {
            $newQty = min($book['stock'], $existing['quantity'] + $qty);
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQty, $existing['id']]);
        } else {
            $qty = min($qty, $book['stock']);
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $book['id'], $qty]);
        }
        flash('success', '"' . $book['title'] . '" ditambahkan ke keranjang.');
    }
    header('Location: book_detail.php?id=' . $book['id']);
    exit;
}

$inWish = isInWishlist($pdo, $book['id']);
require_once 'includes/header.php';
?>
<div class="container">
  <div class="book-detail">
    <div>
      <?php if (!empty($book['cover_image'])): ?>
        <div class="book-detail-cover has-image" style="background:<?= e($book['cover_color']) ?>;">
          <img src="uploads/covers/<?= e($book['cover_image']) ?>" alt="<?= e($book['title']) ?>">
        </div>
      <?php else: ?>
        <div class="book-detail-cover" style="background:<?= e($book['cover_color']) ?>;">
          <?= e($book['title']) ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="book-detail-info">
      <span class="cat-pill"><?= e($book['category']) ?></span>
      <h1><?= e($book['title']) ?></h1>
      <p class="author">oleh <?= e($book['author']) ?></p>
      <p class="price"><?= formatRupiah($book['price']) ?></p>
      <p class="desc"><?= nl2br(e($book['description'])) ?></p>
      <p class="stock-info">
        <?= $book['stock'] > 0 ? 'Stok tersedia: ' . (int)$book['stock'] : '<span style="color:var(--error);font-weight:700;">Stok habis</span>' ?>
      </p>

      <?php if ($book['stock'] > 0): ?>
      <form method="POST">
        <div class="qty-select">
          <button type="button" onclick="stepQty(-1)">-</button>
          <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="<?= (int)$book['stock'] ?>">
          <button type="button" onclick="stepQty(1)">+</button>
        </div>
        <div class="detail-actions">
          <button type="submit" name="add_to_cart" value="1" class="btn btn-primary">Tambah ke Keranjang</button>
          <button type="submit" name="buy_now" value="1" class="btn btn-buy">Checkout</button>
          <?php if (isLoggedIn() && !isAdmin()): ?>
            <button type="button" class="btn btn-outline" onclick="document.getElementById('wishForm').submit()">
              <?= $inWish ? '&#9829; Di Wishlist' : '&#9825; Tambah Wishlist' ?>
            </button>
          <?php endif; ?>
        </div>
      </form>
      <form id="wishForm" action="wishlist_action.php" method="POST" style="display:none;">
        <input type="hidden" name="book_id" value="<?= (int)$book['id'] ?>">
        <input type="hidden" name="redirect" value="book_detail.php?id=<?= (int)$book['id'] ?>">
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function stepQty(delta) {
  var input = document.getElementById('qtyInput');
  var max = parseInt(input.max || 999);
  var val = parseInt(input.value || 1) + delta;
  if (val < 1) val = 1;
  if (val > max) val = max;
  input.value = val;
}
</script>

<?php require_once 'includes/footer.php'; ?>
