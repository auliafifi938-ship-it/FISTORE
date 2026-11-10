<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle = 'Forum';

$topics = [
  ['title' => 'Rekomendasi buku pengembangan diri untuk pemula?', 'author' => 'Dinda', 'replies' => 12],
  ['title' => 'Diskusi buku "Atomic Habits" - bab 3', 'author' => 'Reza', 'replies' => 8],
  ['title' => 'Tips membaca lebih cepat, ada yang punya trik?', 'author' => 'Sari', 'replies' => 21],
  ['title' => 'Buku fiksi terbaik yang kamu baca tahun ini', 'author' => 'Fajar', 'replies' => 15],
];

require_once 'includes/header.php';
?>
<div class="container">
  <div class="page-header">
    <h1>Forum Diskusi</h1>
    <p>Ngobrol seputar buku bareng pembaca lain</p>
  </div>

  <div style="padding-bottom:60px;">
    <?php foreach ($topics as $t): ?>
      <div class="cart-item" style="grid-template-columns:1fr auto;">
        <div>
          <div class="ci-title"><?= e($t['title']) ?></div>
          <div class="ci-price">oleh <?= e($t['author']) ?></div>
        </div>
        <div class="badge-role badge-user"><?= (int)$t['replies'] ?> balasan</div>
      </div>
    <?php endforeach; ?>
    <p class="form-hint mt-20">Fitur forum lengkap (posting & balasan) dapat dikembangkan lebih lanjut sesuai kebutuhan.</p>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
