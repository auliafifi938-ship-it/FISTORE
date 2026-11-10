<?php
// Bagian "Pembayaran" di struk untuk Transfer Bank & E-Wallet.
// Dipakai di receipt.php (butuh $order).
$isEwallet = isEwalletMethod($order['payment_method']);
$canUpload = orderCanUploadProof($order);
?>
<div class="receipt-divider"></div>
<div class="proof-section">
  <h3 class="proof-title">Pembayaran</h3>

  <?php if ($isEwallet): ?>
    <?php $qrLabel = ewalletName($order['payment_method']) ?: 'e-wallet'; include __DIR__ . '/qr_frame.php'; ?>
    <p class="form-hint text-center">Bayar sebesar <b><?= formatRupiah($order['total_amount']) ?></b><?= $canUpload ? ', lalu unggah bukti pembayarannya di bawah' : '' ?>.</p>
  <?php else: ?>
    <p class="form-hint" style="margin:0 0 12px;">Transfer sebesar <b><?= formatRupiah($order['total_amount']) ?></b> ke rekening tujuan di atas<?= $canUpload ? ', lalu unggah bukti transfernya di bawah' : '' ?>.</p>
  <?php endif; ?>

  <?php if ($order['payment_status'] === 'Ditolak' && $order['payment_note']): ?>
    <p class="proof-note">Bukti ditolak admin: <?= e($order['payment_note']) ?>. Silakan upload ulang.</p>
  <?php endif; ?>

  <?php if ($order['payment_proof']): ?>
    <div class="proof-current no-print">
      <?= proofViewHtml($order['payment_proof'], 'uploads/proofs/') ?>
      <div class="form-hint">
        Dikirim oleh <b><?= e($order['payment_sender']) ?></b>
        <?php if ($order['payment_proof_at']): ?> pada <?= date('d M Y, H:i', strtotime($order['payment_proof_at'])) ?><?php endif; ?>.
        <?php if ($order['payment_status'] === 'Menunggu Verifikasi'): ?> Menunggu dikonfirmasi admin.<?php endif; ?>
        <?php if ($order['payment_status'] === 'Terverifikasi'): ?> Pembayaran sudah dikonfirmasi admin.<?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($canUpload): ?>
    <form method="POST" action="upload_proof.php" enctype="multipart/form-data" class="proof-form no-print">
      <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
      <div class="form-group">
        <label>Nama Pengirim / Pemilik <?= $isEwallet ? 'Akun E-Wallet' : 'Rekening' ?></label>
        <input type="text" name="payment_sender" required maxlength="100" value="<?= e($order['payment_sender'] ?? $order['buyer_name']) ?>">
      </div>
      <div class="form-group">
        <label>Foto / Screenshot Bukti Pembayaran</label>
        <input type="file" name="payment_proof" id="proofFile" accept="image/jpeg,image/png,image/webp,application/pdf" required>
        <div class="form-hint">JPG, PNG, WEBP, atau PDF. Gambar otomatis dikompres dan diubah jadi PDF.</div>
        <img id="proofPreview" class="proof-preview" alt="" style="display:none;">
      </div>
      <button type="submit" class="btn btn-primary btn-block"><?= $order['payment_proof'] ? 'Kirim Ulang Bukti' : 'Kirim Bukti Pembayaran' ?></button>
    </form>
    <script>
    document.getElementById('proofFile').addEventListener('change', function () {
      var img = document.getElementById('proofPreview');
      var f = this.files && this.files[0];
      if (f && f.type.indexOf('image/') === 0) { img.src = URL.createObjectURL(f); img.style.display = 'block'; } else { img.style.display = 'none'; }
    });
    </script>
  <?php endif; ?>
</div>
