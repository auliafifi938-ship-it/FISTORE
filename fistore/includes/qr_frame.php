<?php
// Frame QR code e-wallet. Sementara hanya frame kosong.
// Nanti tinggal taruh gambar QR di assets/img/qr-ewallet.png, otomatis tampil di sini.
$qrFile = __DIR__ . '/../assets/img/qr-ewallet.png';
$qrSrc  = is_file($qrFile) ? base_url('assets/img/qr-ewallet.png') : null;
?>
<div class="qr-box">
  <div class="qr-frame">
    <span class="qr-corner tl"></span><span class="qr-corner tr"></span>
    <span class="qr-corner bl"></span><span class="qr-corner br"></span>
    <?php if ($qrSrc): ?>
      <img src="<?= e($qrSrc) ?>" alt="QR Code pembayaran">
    <?php else: ?>
      <div class="qr-placeholder">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><path d="M14 14h3v3h-3zM20 14v3M14 20h3M20 20h1"/></svg>
        <span>QR Code</span>
      </div>
    <?php endif; ?>
  </div>
  <div class="qr-caption">Scan QR ini dengan aplikasi <b><?= e($qrLabel ?? 'e-wallet') ?></b> kamu</div>
</div>
