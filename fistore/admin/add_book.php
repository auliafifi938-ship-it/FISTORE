<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Tambah Buku';

$error = '';
$categoryList = $pdo->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);
$colors = ['#f7a1b5','#e8617e','#4a4a68','#e8dcc8','#3a6ea5','#e8b04b','#2b2b2b','#f2c9c2','#f4a300','#2f5d8a','#1c1c2e','#c0392b','#dfe6e9','#1a1a1a','#8e2f2f','#5aa06a','#f1c40f'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $coverColor = $_POST['cover_color'] ?? '#f7a1b5';
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $isTrending = isset($_POST['is_trending']) ? 1 : 0;

    if ($title === '' || $author === '' || $category === '' || $price <= 0) {
        $error = 'Judul, penulis, kategori, dan harga wajib diisi dengan benar.';
    } else {
        $uploadDir = __DIR__ . '/../uploads/covers';
        $upload = handleCoverUpload($_FILES['cover_photo'] ?? null, $uploadDir);

        if (!$upload['ok']) {
            $error = $upload['error'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, category, price, stock, description, cover_color, cover_image, is_new, is_bestseller, is_trending) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$title, $author, $category, $price, $stock, $description, $coverColor, $upload['filename'], $isNew, $isBestseller, $isTrending]);
            flash('success', 'Buku "' . $title . '" berhasil ditambahkan.');
            header('Location: books.php');
            exit;
        }
    }
}

require_once 'includes/admin_header.php';
?>

<?php if ($error): ?><div class="flash flash-error" style="margin:0 0 18px;"><?= e($error) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="max-width:640px;background:#fff;padding:26px;border-radius:14px;border:1px solid var(--border-color);">
  <div class="form-group">
    <label>Judul Buku</label>
    <input type="text" name="title" required value="<?= e($_POST['title'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label>Penulis</label>
    <input type="text" name="author" required value="<?= e($_POST['author'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label>Kategori</label>
    <select name="category" required>
      <option value="">-- Pilih kategori --</option>
      <?php foreach ($categoryList as $cat): ?>
        <option value="<?= e($cat) ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-hint">Belum ada kategori yang cocok? <a href="categories.php">Tambah kategori baru</a>.</div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="form-group">
      <label>Harga (Rp)</label>
      <input type="number" name="price" required min="0" value="<?= e($_POST['price'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label>Stok</label>
      <input type="number" name="stock" required min="0" value="<?= e($_POST['stock'] ?? 0) ?>">
    </div>
  </div>
  <div class="form-group">
    <label>Deskripsi</label>
    <textarea name="description" rows="4"><?= e($_POST['description'] ?? '') ?></textarea>
  </div>
  <div class="form-group">
    <label>Foto Sampul (opsional)</label>
    <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp,image/gif">
    <div class="form-hint">Format JPG/PNG/WEBP/GIF, maks 2MB. Kalau tidak diisi, kartu buku akan memakai warna cover di bawah ini.</div>
  </div>
  <div class="form-group">
    <label>Warna Cover</label>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php foreach ($colors as $c): ?>
        <label style="cursor:pointer;">
          <input type="radio" name="cover_color" value="<?= $c ?>" style="display:none;" <?= ($_POST['cover_color'] ?? '') === $c ? 'checked' : '' ?> onclick="document.querySelectorAll('.color-dot').forEach(d=>d.style.outline='none');this.nextElementSibling.style.outline='2px solid #333'">
          <span class="color-dot" style="display:inline-block;width:28px;height:28px;border-radius:50%;background:<?= $c ?>;"></span>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="form-hint">Klik salah satu warna sebagai tampilan cover buku.</div>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_new" value="1"> Tampilkan di "What's New"</label>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_bestseller" value="1"> Tampilkan di "Best Seller"</label>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_trending" value="1"> Tampilkan di "Trending Now"</label>
  </div>
  <button type="submit" class="btn btn-primary">Simpan Buku</button>
  <a href="books.php" class="btn btn-outline">Batal</a>
</form>

<?php require_once 'includes/admin_footer.php'; ?>
