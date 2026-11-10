<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Edit Buku';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    flash('error', 'Buku tidak ditemukan.');
    header('Location: books.php');
    exit;
}

$colors = ['#f7a1b5','#e8617e','#4a4a68','#e8dcc8','#3a6ea5','#e8b04b','#2b2b2b','#f2c9c2','#f4a300','#2f5d8a','#1c1c2e','#c0392b','#dfe6e9','#1a1a1a','#8e2f2f','#5aa06a','#f1c40f'];
$error = '';
$categoryList = $pdo->query("SELECT name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $coverColor = $_POST['cover_color'] ?? $book['cover_color'];
    $isNew = isset($_POST['is_new']) ? 1 : 0;
    $isBestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $isTrending = isset($_POST['is_trending']) ? 1 : 0;

    if ($title === '' || $author === '' || $category === '' || $price <= 0) {
        $error = 'Judul, penulis, kategori, dan harga wajib diisi dengan benar.';
    } else {
        $uploadDir = __DIR__ . '/../uploads/covers';
        $coverImage = $book['cover_image']; // pertahankan foto lama secara default

        if (isset($_POST['remove_photo']) && $coverImage) {
            deleteCoverImage($coverImage, $uploadDir);
            $coverImage = null;
        }

        $upload = handleCoverUpload($_FILES['cover_photo'] ?? null, $uploadDir);
        if (!$upload['ok']) {
            $error = $upload['error'];
        } else {
            if ($upload['filename']) {
                // foto baru diunggah -> hapus foto lama, pakai yang baru
                if ($coverImage) {
                    deleteCoverImage($coverImage, $uploadDir);
                }
                $coverImage = $upload['filename'];
            }

            $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, category=?, price=?, stock=?, description=?, cover_color=?, cover_image=?, is_new=?, is_bestseller=?, is_trending=? WHERE id=?");
            $stmt->execute([$title, $author, $category, $price, $stock, $description, $coverColor, $coverImage, $isNew, $isBestseller, $isTrending, $id]);
            flash('success', 'Buku "' . $title . '" berhasil diperbarui.');
            header('Location: books.php');
            exit;
        }
    }
    $book = array_merge($book, compact('title','author','category','price','stock','description','coverColor','isNew','isBestseller','isTrending'));
}

require_once 'includes/admin_header.php';
?>

<?php if ($error): ?><div class="flash flash-error" style="margin:0 0 18px;"><?= e($error) ?></div><?php endif; ?>

<form method="POST" enctype="multipart/form-data" style="max-width:640px;background:#fff;padding:26px;border-radius:14px;border:1px solid var(--border-color);">
  <div class="form-group">
    <label>Judul Buku</label>
    <input type="text" name="title" required value="<?= e($book['title']) ?>">
  </div>
  <div class="form-group">
    <label>Penulis</label>
    <input type="text" name="author" required value="<?= e($book['author']) ?>">
  </div>
  <div class="form-group">
    <label>Kategori</label>
    <?php
      // jaga-jaga kalau kategori lama buku tidak ada di tabel categories
      $list = $categoryList;
      if ($book['category'] !== '' && !in_array($book['category'], $list, true)) $list[] = $book['category'];
    ?>
    <select name="category" required>
      <?php foreach ($list as $cat): ?>
        <option value="<?= e($cat) ?>" <?= $book['category'] === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
      <?php endforeach; ?>
    </select>
    <div class="form-hint"><a href="categories.php">Kelola kategori</a></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="form-group">
      <label>Harga (Rp)</label>
      <input type="number" name="price" required min="0" value="<?= e($book['price']) ?>">
    </div>
    <div class="form-group">
      <label>Stok</label>
      <input type="number" name="stock" required min="0" value="<?= e($book['stock']) ?>">
    </div>
  </div>
  <div class="form-group">
    <label>Deskripsi</label>
    <textarea name="description" rows="4"><?= e($book['description']) ?></textarea>
  </div>
  <div class="form-group">
    <label>Foto Sampul (opsional)</label>
    <?php if (!empty($book['cover_image'])): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <img src="../uploads/covers/<?= e($book['cover_image']) ?>" alt="" style="width:60px;height:80px;object-fit:cover;border-radius:8px;border:1px solid var(--border-color);">
        <label style="font-weight:600;font-size:13px;"><input type="checkbox" name="remove_photo" value="1"> Hapus foto ini</label>
      </div>
    <?php endif; ?>
    <input type="file" name="cover_photo" accept="image/jpeg,image/png,image/webp,image/gif">
    <div class="form-hint">Format JPG/PNG/WEBP/GIF, maks 2MB. Unggah file baru untuk mengganti foto yang ada.</div>
  </div>
  <div class="form-group">
    <label>Warna Cover</label>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php foreach ($colors as $c): ?>
        <label style="cursor:pointer;">
          <input type="radio" name="cover_color" value="<?= $c ?>" style="display:none;" <?= strtolower($book['cover_color']) === $c ? 'checked' : '' ?> onclick="document.querySelectorAll('.color-dot').forEach(d=>d.style.outline='none');this.nextElementSibling.style.outline='2px solid #333'">
          <span class="color-dot" style="display:inline-block;width:28px;height:28px;border-radius:50%;background:<?= $c ?>;outline:<?= strtolower($book['cover_color']) === $c ? '2px solid #333' : 'none' ?>;"></span>
        </label>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_new" value="1" <?= $book['is_new'] ? 'checked' : '' ?>> Tampilkan di "What's New"</label>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_bestseller" value="1" <?= $book['is_bestseller'] ? 'checked' : '' ?>> Tampilkan di "Best Seller"</label>
  </div>
  <div class="form-group">
    <label><input type="checkbox" name="is_trending" value="1" <?= $book['is_trending'] ? 'checked' : '' ?>> Tampilkan di "Trending Now"</label>
  </div>
  <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
  <a href="books.php" class="btn btn-outline">Batal</a>
</form>

<?php require_once 'includes/admin_footer.php'; ?>
