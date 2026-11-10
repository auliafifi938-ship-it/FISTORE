<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();

if (isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Pesanan tidak ditemukan.');
    header('Location: orders.php');
    exit;
}

$back = 'receipt.php?order_id=' . $orderId;

if (!orderCanUploadProof($order)) {
    flash('error', 'Bukti pembayaran untuk pesanan ini tidak bisa diubah lagi.');
    header('Location: ' . $back);
    exit;
}

$sender = trim($_POST['payment_sender'] ?? '');
if ($sender === '') {
    flash('error', 'Nama pengirim wajib diisi.');
    header('Location: ' . $back);
    exit;
}

$proofDir = __DIR__ . '/uploads/proofs';
$upload = handleProofUpload($_FILES['payment_proof'] ?? null, $proofDir, [
    'invoice' => $order['invoice_code'], 'sender' => $sender, 'method' => $order['payment_method'], 'amount' => $order['total_amount'],
]);
if (!$upload['ok']) {
    flash('error', $upload['error']);
    header('Location: ' . $back);
    exit;
}

$pdo->prepare("UPDATE orders SET payment_proof = ?, payment_sender = ?, payment_proof_at = NOW(), payment_status = 'Menunggu Verifikasi', payment_note = NULL WHERE id = ?")
    ->execute([$upload['filename'], mb_substr($sender, 0, 100), $orderId]);

// Hapus bukti lama kalau user upload ulang
deleteProofImage($order['payment_proof'], $proofDir);

flash('success', 'Bukti pembayaran terkirim. Admin akan memeriksa dan mengonfirmasi pesananmu.');
header('Location: ' . $back);
exit;
