<?php
// =========================================================
// FISTORE - Konfigurasi Database & Session
// =========================================================
// Admin (folder /admin) dan area customer memakai NAMA SESSION
// yang berbeda, supaya login admin & login user bisa aktif
// bersamaan di browser yang sama tanpa saling menimpa.
if (session_status() === PHP_SESSION_NONE) {
    $isAdminArea = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '/admin/') !== false;
    session_name($isAdminArea ? 'FISTORE_ADMIN_SESSID' : 'FISTORE_SESSID');
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'fistore_db');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal. Pastikan XAMPP (MySQL) aktif dan database 'fistore_db' sudah di-import. Detail: " . $e->getMessage());
}
