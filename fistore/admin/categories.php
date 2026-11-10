<?php
require_once '../config.php';
require_once '../includes/functions.php';
requireAdmin();
$pageTitle = 'Kategori Buku';

// ---- Hapus kategori ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $cat = $stmt->fetch();

    if ($cat) {
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE category = ?");
        $cnt->execute([$cat['name']]);
        if ($cnt->fetchColumn() > 0) {
            flash('error', 'Kategori "' . $cat['name'] . '" tidak bisa dihapus karena masih dipakai buku.');
        } else {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            flash('success', 'Kategori berhasil dihapus.');
        }
    }
    header('Location: categories.php');
    exit;
}

// ---- Mode edit ----
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

// ---- Tambah / Edit kategori ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $id   = (int)($_POST['id'] ?? 0);

    if ($name === '') {
        flash('error', 'Nama kategori wajib diisi.');
    } else {
        $chk = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id <> ?");
        $chk->execute([$name, $id]);
        if ($chk->fetch()) {
            flash('error', 'Kategori "' . $name . '" sudah ada.');
        } elseif ($id > 0) {
            $old = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
            $old->execute([$id]);
            $oldName = $old->fetchColumn();

            $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?")->execute([$name, $id]);
            if ($oldName !== false) {
                $pdo->prepare("UPDATE books SET category = ? WHERE category = ?")->execute([$name, $oldName]);
            }
            flash('success', 'Kategori berhasil diperbarui.');
        } else {
            $pdo->prepare("INSERT INTO categories (name) VALUES (?)")->execute([$name]);
            flash('success', 'Kategori "' . $name . '" berhasil ditambahkan.');
        }
    }
    header('Location: categories.php');
    exit;
}

// ---- Daftar kategori + jumlah buku ----
$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM books b WHERE b.category = c.name) AS total_books
    FROM categories c ORDER BY c.name ASC
")->fetchAll();

require_once 'includes/admin_header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:24px;align-items:start;">

  <form method="POST" style="background:#fff;padding:26px;border-radius:14px;border:1px solid var(--border-color);">
    <h3 style="margin-bottom:14px;"><?= $editing ? 'Edit Kategori' : 'Tambah Kategori' ?></h3>
    <input type="hidden" name="id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">
    <div class="form-group">
      <label>Nama Kategori</label>
      <input type="text" name="name" required maxlength="100"
             value="<?= e($editing['name'] ?? '') ?>" placeholder="Fiksi, Sains, Pengembangan Diri, dsb.">
      <?php if ($editing): ?>
        <div class="form-hint">Mengganti nama akan ikut mengubah kategori di semua buku yang memakainya.</div>
      <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary"><?= $editing ? 'Simpan Perubahan' : 'Tambah' ?></button>
    <?php if ($editing): ?><a href="categories.php" class="btn btn-outline">Batal</a><?php endif; ?>
  </form>

  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>Nama Kategori</th><th>Jumlah Buku</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php if (empty($categories)): ?>
          <tr><td colspan="3">Belum ada kategori.</td></tr>
        <?php endif; ?>
        <?php foreach ($categories as $c): ?>
          <tr>
            <td><?= e($c['name']) ?></td>
            <td><?= (int)$c['total_books'] ?></td>
            <td style="white-space:nowrap;">
              <a href="categories.php?edit=<?= (int)$c['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
              <a href="categories.php?delete=<?= (int)$c['id'] ?>" class="btn btn-danger btn-sm"
                 onclick="return confirm('Hapus kategori ini?');">Hapus</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php require_once 'includes/admin_footer.php'; ?>
