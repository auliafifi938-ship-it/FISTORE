-- =========================================================
-- FISTORE - Migrasi: tambah dukungan foto sampul buku
-- Jalankan file ini lewat phpMyAdmin (tab SQL) HANYA JIKA
-- kamu sudah pernah import database.sql versi sebelumnya
-- (yang belum punya kolom cover_image).
--
-- Kalau kamu baru pertama kali install, TIDAK PERLU menjalankan
-- file ini — cukup import database.sql yang terbaru.
-- =========================================================

USE fistore_db;

ALTER TABLE books
ADD COLUMN cover_image VARCHAR(255) DEFAULT NULL AFTER cover_color;
