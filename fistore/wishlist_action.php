<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();

$bookId = (int)($_POST['book_id'] ?? 0);
$redirect = $_POST['redirect'] ?? 'wishlist.php';

if (!isAdmin() && $bookId > 0) {
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$_SESSION['user_id'], $bookId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
        $stmt->execute([$existing['id']]);
        flash('success', 'Dihapus dari wishlist.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $bookId]);
        flash('success', 'Ditambahkan ke wishlist.');
    }
}

// Cegah open redirect: hanya izinkan path relatif lokal
if (strpos($redirect, '//') === 0 || strpos($redirect, 'http') === 0) {
    $redirect = 'wishlist.php';
}
header('Location: ' . $redirect);
exit;
