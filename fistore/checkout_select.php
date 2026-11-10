<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();

if (isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$selected = $_POST['selected'] ?? [];
$selected = array_values(array_unique(array_map('intval', $selected)));

if (empty($selected)) {
    flash('error', 'Pilih minimal satu buku untuk checkout.');
    header('Location: cart.php');
    exit;
}

// Pastikan semua cart_id yang dipilih benar-benar milik user ini
$placeholders = implode(',', array_fill(0, count($selected), '?'));
$params = array_merge($selected, [$_SESSION['user_id']]);
$stmt = $pdo->prepare("SELECT id FROM cart WHERE id IN ($placeholders) AND user_id = ?");
$stmt->execute($params);
$validIds = array_map('intval', array_column($stmt->fetchAll(), 'id'));

if (empty($validIds)) {
    flash('error', 'Buku yang dipilih tidak valid.');
    header('Location: cart.php');
    exit;
}

$_SESSION['checkout_cart_ids'] = $validIds;
unset($_SESSION['buy_now']);
header('Location: checkout.php');
exit;
