<?php
// Field bukti pembayaran di halaman checkout. Butuh $proofPrefix ('bank' / 'ewallet') dan $proofUser (nama default).
?>
<div class="proof-box">
  <h4 class="proof-title">Bukti Pembayaran</h4>
  <div class="form-group">
    <label>Nama Pengirim / Pemilik <?= $proofPrefix === 'bank' ? 'Rekening' : 'Akun E-Wallet' ?></label>
    <input type="text" class="proof-field" name="<?= $proofPrefix ?>_sender" maxlength="100" value="<?= e($proofUser) ?>">
  </div>
  <div class="form-group" style="margin:0;">
    <label>Foto / Screenshot Bukti Pembayaran</label>
    <input type="file" class="proof-field proof-file" name="<?= $proofPrefix ?>_proof" accept="image/jpeg,image/png,image/webp,application/pdf">
    <div class="form-hint">JPG, PNG, WEBP, atau PDF — gambar otomatis dikompres dan diubah jadi PDF. Admin akan memeriksa bukti ini sebelum pesananmu diproses.</div>
    <img class="proof-preview" alt="" style="display:none;">
  </div>
</div>
