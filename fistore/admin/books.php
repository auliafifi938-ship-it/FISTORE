<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Kelola Buku';

// Hapus buku
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT cover_image FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$id]);

        if ($row && $row['cover_image']) {
            deleteCoverImage($row['cover_image'], __DIR__ . '/../uploads/covers');
        }
        flash('success', 'Buku berhasil dihapus.');
    } catch (Exception $e) {
        flash('error', 'Buku tidak bisa dihapus karena masih ada di riwayat transaksi.');
    }
    header('Location: books.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM books";
$params = [];
if ($q !== '') {
    $sql .= " WHERE title LIKE ? OR author LIKE ?";
    $params = ["%$q%", "%$q%"];
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

require_once 'includes/admin_header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
  <form method="GET" style="display:flex;gap:8px;">
    <input type="text" name="q" placeholder="Cari judul / penulis..." value="<?= e($q) ?>" style="padding:9px 14px;border:1.5px solid var(--border-color);border-radius:8px;min-width:220px;">
    <button type="submit" class="btn btn-outline btn-sm">Cari</button>
  </form>
  <a href="add_book.php" class="btn btn-primary">+ Tambah Buku</a>
</div>

<div style="overflow-x:auto;">
<table class="data-table">
  <thead>
    <tr><th>Cover</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Tag</th><th>Aksi</th></tr>
  </thead>
  <tbody>
    <?php if (empty($books)): ?>
      <tr><td colspan="8">Belum ada buku. Tambahkan buku pertama kamu.</td></tr>
    <?php endif; ?>
    <?php foreach ($books as $b): ?>
      <tr>
        <td>
          <?php if (!empty($b['cover_image'])): ?>
            <img src="../uploads/covers/<?= e($b['cover_image']) ?>" class="table-thumb" alt="">
          <?php else: ?>
            <div class="table-thumb-placeholder" style="background:<?= e($b['cover_color']) ?>;">N/A</div>
          <?php endif; ?>
        </td>
        <td><?= e($b['title']) ?></td>
        <td><?= e($b['author']) ?></td>
        <td><?= e($b['category']) ?></td>
        <td><?= formatRupiah($b['price']) ?></td>
        <td><?= (int)$b['stock'] ?></td>
        <td style="font-size:11px;">
          <?= $b['is_new'] ? '<span class="badge-role badge-user">Baru</span> ' : '' ?>
          <?= $b['is_bestseller'] ? '<span class="badge-role badge-admin">Best</span> ' : '' ?>
          <?= $b['is_trending'] ? '<span class="badge-role badge-user">Trending</span>' : '' ?>
        </td>
        <td style="white-space:nowrap;">
          <a href="edit_book.php?id=<?= (int)$b['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
          <a href="books.php?delete=<?= (int)$b['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus buku ini?');">Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
