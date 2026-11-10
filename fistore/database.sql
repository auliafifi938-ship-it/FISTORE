-- =========================================================
-- FISTORE - Database Schema & Seed Data
-- Import file ini lewat phpMyAdmin (XAMPP) sebelum menjalankan web
-- =========================================================

CREATE DATABASE IF NOT EXISTS fistore_db;
USE fistore_db;

-- ---------------------------------------------------------
-- Tabel users
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Tabel books
-- ---------------------------------------------------------
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category VARCHAR(60) NOT NULL,
    price INT NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT,
    cover_color VARCHAR(20) DEFAULT '#f7a1b5',
    cover_image VARCHAR(255) DEFAULT NULL,
    is_new TINYINT(1) DEFAULT 0,
    is_bestseller TINYINT(1) DEFAULT 0,
    is_trending TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Tabel wishlist
-- ---------------------------------------------------------
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wish (user_id, book_id)
);

-- ---------------------------------------------------------
-- Tabel cart
-- ---------------------------------------------------------
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, book_id)
);

-- ---------------------------------------------------------
-- Tabel orders (transaksi)
-- ---------------------------------------------------------
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_code VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    total_amount INT NOT NULL,
    payment_method VARCHAR(50) DEFAULT 'Transfer Bank',
    buyer_name VARCHAR(100),
    buyer_address TEXT,
    status VARCHAR(30) DEFAULT 'Menunggu Konfirmasi',
    payment_status VARCHAR(30) DEFAULT NULL,
    payment_sender VARCHAR(100) DEFAULT NULL,
    payment_proof VARCHAR(255) DEFAULT NULL,
    payment_proof_at TIMESTAMP NULL DEFAULT NULL,
    payment_note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Tabel order_items
-- ---------------------------------------------------------
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    price INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Catatan: akun admin & user demo TIDAK dibuat lewat SQL ini,
-- karena password harus di-hash oleh PHP (password_hash).
-- Setelah import database ini, buka setup.php sekali lewat
-- browser (http://localhost/fistore/setup.php) untuk otomatis
-- membuat akun demo:
--   Admin -> admin@fistore.com / admin123
--   User  -> user@fistore.com  / user123
-- Atau silakan daftar akun baru sendiri lewat halaman register.php
-- ---------------------------------------------------------
-- Seed: buku (mengikuti mockup)
-- ---------------------------------------------------------
INSERT INTO books (title, author, category, price, stock, description, cover_color, is_new, is_bestseller, is_trending) VALUES
('Justice', 'Michael J. Sandel', 'Filsafat', 75000, 20, 'Sebuah eksplorasi mendalam tentang keadilan, moralitas, dan bagaimana kita seharusnya hidup bersama.', '#4a4a68', 1, 0, 0),
('Sapiens', 'Yuval Noah Harari', 'Sejarah', 120000, 15, 'Riwayat singkat umat manusia dari zaman batu hingga era modern.', '#e8dcc8', 1, 0, 0),
('Gadapoe Sri', 'Penulis Lokal', 'Fiksi', 67000, 10, 'Kisah menyentuh tentang kehidupan dan perjuangan seorang perempuan.', '#3a6ea5', 1, 0, 0),
('Ikigai', 'Hector Garcia', 'Pengembangan Diri', 67000, 25, 'Rahasia panjang umur dan kebahagiaan ala orang Jepang.', '#e8b04b', 1, 0, 0),
('Logicomix', 'Apostolos Doxiadis', 'Graphic Novel', 85000, 8, 'Novel grafis epik tentang pencarian kebenaran logika.', '#2b2b2b', 1, 0, 0),
('Emily in Paris', 'Penulis Populer', 'Fiksi', 65000, 12, 'Kisah ringan dan menghibur seputar kehidupan di Paris.', '#f2c9c2', 1, 0, 0),
('Atomic Habits', 'James Clear', 'Pengembangan Diri', 112000, 30, 'Perubahan kecil yang memberi hasil luar biasa lewat kebiasaan.', '#f4a300', 0, 1, 1),
('Sang Alkemis', 'Paulo Coelho', 'Fiksi', 75000, 18, 'Perjalanan seorang gembala mengejar mimpi dan takdirnya.', '#2f5d8a', 0, 1, 0),
('Bill Bryson - A Short History', 'Bill Bryson', 'Sains', 47000, 14, 'Penjelasan sains yang menghibur tentang alam semesta dan kehidupan.', '#1c1c2e', 0, 1, 0),
('The Little Prince', 'Antoine de Saint-Exupery', 'Fiksi', 47000, 22, 'Kisah klasik tentang persahabatan dan makna kehidupan.', '#fdfaf5', 0, 1, 0),
('Elmet', 'Fiona Mozley', 'Fiksi', 65000, 9, 'Novel sastra tentang keluarga dan tanah kelahiran.', '#c0392b', 0, 1, 0),
('Psychology of Money', 'Morgan Housel', 'Keuangan', 65000, 26, 'Pelajaran abadi tentang kekayaan, keserakahan, dan kebahagiaan.', '#dfe6e9', 0, 1, 1),
('Psychology of Money (Trending)', 'Morgan Housel', 'Keuangan', 65000, 20, 'Edisi trending dari buku psikologi uang paling laris.', '#dfe6e9', 0, 0, 1),
('The Mountain Is You', 'Brianna Wiest', 'Pengembangan Diri', 62000, 17, 'Mengatasi penyabotase diri untuk bertumbuh dan berubah.', '#1a1a1a', 0, 0, 1),
('The Housemaid', 'Freida McFadden', 'Thriller', 90000, 11, 'Thriller psikologis yang penuh kejutan tak terduga.', '#8e2f2f', 0, 0, 1),
('The Let Them Theory', 'Mel Robbins', 'Pengembangan Diri', 115000, 19, 'Teori sederhana untuk hidup lebih bahagia dan bebas.', '#5aa06a', 0, 0, 1),
('Atomic Habits (Trending)', 'James Clear', 'Pengembangan Diri', 112000, 24, 'Stok tambahan Atomic Habits karena permintaan tinggi.', '#f4a300', 0, 0, 1),
('Yellowface', 'R. F. Kuang', 'Fiksi', 99000, 13, 'Satir tajam tentang dunia penerbitan dan identitas.', '#f1c40f', 0, 0, 1);
