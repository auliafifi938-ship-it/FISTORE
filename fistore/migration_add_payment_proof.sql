-- Jalankan sekali di phpMyAdmin (database fistore_db) -> tab SQL.
-- Menambah kolom bukti pembayaran untuk Transfer Bank & E-Wallet.
ALTER TABLE orders
  ADD COLUMN payment_status VARCHAR(30) DEFAULT NULL AFTER status,
  ADD COLUMN payment_sender VARCHAR(100) DEFAULT NULL AFTER payment_status,
  ADD COLUMN payment_proof VARCHAR(255) DEFAULT NULL AFTER payment_sender,
  ADD COLUMN payment_proof_at TIMESTAMP NULL DEFAULT NULL AFTER payment_proof,
  ADD COLUMN payment_note VARCHAR(255) DEFAULT NULL AFTER payment_proof_at;

-- Pesanan lama: Transfer Bank / E-Wallet yang sudah diproses/selesai dianggap terverifikasi,
-- yang masih menunggu dianggap belum dibayar. COD dibiarkan NULL (tidak butuh bukti).
UPDATE orders SET payment_status = 'Terverifikasi'
  WHERE (payment_method LIKE 'Transfer Bank%' OR payment_method LIKE 'E-Wallet%') AND status IN ('Diproses','Selesai');
UPDATE orders SET payment_status = 'Belum Dibayar'
  WHERE (payment_method LIKE 'Transfer Bank%' OR payment_method LIKE 'E-Wallet%') AND status = 'Menunggu Konfirmasi';
