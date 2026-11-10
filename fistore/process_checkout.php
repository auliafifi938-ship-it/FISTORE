<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();

if (isAdmin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$buyerName = trim($_POST['buyer_name'] ?? '');
$buyerAddress = trim($_POST['buyer_address'] ?? '');
$buyerPhone = trim($_POST['buyer_phone'] ?? '');
$method = $_POST['payment_method'] ?? 'Transfer Bank';
if (!in_array($method, ['Transfer Bank', 'Kartu Kredit', 'E-Wallet', 'COD'], true)) {
    $method = 'Transfer Bank';
}
$paymentMethod = $method;

if ($method === 'Transfer Bank') {
    $bank = $_POST['bank'] ?? 'BCA';
    if (!in_array($bank, ['BCA', 'BNI', 'BRI', 'Mandiri'], true)) {
        $bank = 'BCA';
    }
    $paymentMethod = 'Transfer Bank - ' . $bank;
} elseif ($method === 'E-Wallet') {
    $ewallet = $_POST['ewallet'] ?? 'OVO';
    if (!in_array($ewallet, ['OVO', 'GoPay', 'Dana'], true)) {
        $ewallet = 'OVO';
    }
    $paymentMethod = 'E-Wallet - ' . $ewallet;
} elseif ($method === 'Kartu Kredit') {
    $cardNumber = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
    $cardName = trim($_POST['card_name'] ?? '');
    $cardExpiry = trim($_POST['card_expiry'] ?? '');
    $cardCvv = preg_replace('/\D/', '', $_POST['card_cvv'] ?? '');

    $cardError = '';
    if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19 || !luhnValid($cardNumber)) {
        $cardError = 'Nomor kartu kredit tidak valid.';
    } elseif ($cardName === '') {
        $cardError = 'Nama di kartu wajib diisi.';
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $cardExpiry, $m)
        || mktime(0, 0, 0, (int)$m[1] + 1, 1, 2000 + (int)$m[2]) <= time()) {
        $cardError = 'Masa berlaku kartu tidak valid atau sudah kedaluwarsa.';
    } elseif (strlen($cardCvv) < 3 || strlen($cardCvv) > 4) {
        $cardError = 'CVV harus 3 atau 4 digit.';
    }
    if ($cardError !== '') {
        flash('error', $cardError);
        header('Location: checkout.php');
        exit;
    }
    // Hanya 4 digit terakhir yang disimpan; nomor lengkap & CVV tidak pernah disimpan.
    $paymentMethod = 'Kartu Kredit (**** ' . substr($cardNumber, -4) . ')';
}

if ($buyerName === '' || $buyerAddress === '' || $buyerPhone === '') {
    flash('error', 'Mohon lengkapi semua data penerima.');
    header('Location: checkout.php');
    exit;
}

$buyNow = $_SESSION['buy_now'] ?? null;
$selectedIds = [];

if ($buyNow) {
    // Beli langsung: satu buku, tidak melewati keranjang
    $backUrl = 'book_detail.php?id=' . (int)$buyNow['book_id'];
    $stmt = $pdo->prepare("SELECT id AS book_id, title, price, stock FROM books WHERE id = ?");
    $stmt->execute([(int)$buyNow['book_id']]);
    $book = $stmt->fetch();
    if (!$book) {
        unset($_SESSION['buy_now']);
        flash('error', 'Buku tidak ditemukan.');
        header('Location: books.php');
        exit;
    }
    $items = [array_merge($book, ['cart_id' => 0, 'quantity' => max(1, (int)$buyNow['quantity'])])];
} else {
    $backUrl = 'cart.php';
    $selectedIds = $_SESSION['checkout_cart_ids'] ?? [];
    if (empty($selectedIds)) {
        flash('error', 'Pilih buku yang mau di-checkout dari keranjang dulu.');
        header('Location: cart.php');
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
    $params = array_merge($selectedIds, [$_SESSION['user_id']]);
    $stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity, b.id as book_id, b.title, b.price, b.stock FROM cart c JOIN books b ON c.book_id = b.id WHERE c.id IN ($placeholders) AND c.user_id = ?");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        unset($_SESSION['checkout_cart_ids']);
        flash('error', 'Buku yang dipilih tidak ditemukan di keranjang.');
        header('Location: cart.php');
        exit;
    }
}

// Validasi stok sebelum transaksi
foreach ($items as $it) {
    if ($it['quantity'] > $it['stock']) {
        flash('error', 'Stok "' . $it['title'] . '" tidak mencukupi.');
        header('Location: ' . $backUrl);
        exit;
    }
}

$subtotal = 0;
foreach ($items as $it) { $subtotal += $it['price'] * $it['quantity']; }
$shipping = 15000;
$total = $subtotal + $shipping;
$invoice = generateInvoiceCode();

// Transfer Bank / E-Wallet: bukti pembayaran wajib dikirim bersamaan dengan pesanan.
$proofDir = __DIR__ . '/uploads/proofs';
$proofFile = null;
$proofSender = null;
if (isPrepaidMethod($paymentMethod)) {
    $field = ($method === 'Transfer Bank') ? 'bank' : 'ewallet';
    $proofSender = trim($_POST[$field . '_sender'] ?? '');
    if ($proofSender === '') {
        flash('error', 'Nama pengirim pada bukti pembayaran wajib diisi.');
        header('Location: checkout.php');
        exit;
    }
    $upload = handleProofUpload($_FILES[$field . '_proof'] ?? null, $proofDir, [
        'invoice' => $invoice, 'sender' => $proofSender, 'method' => $paymentMethod, 'amount' => $total,
    ]);
    if (!$upload['ok']) {
        flash('error', $upload['error']);
        header('Location: checkout.php');
        exit;
    }
    $proofFile = $upload['filename'];
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO orders (invoice_code, user_id, total_amount, payment_method, buyer_name, buyer_address, status, payment_status, payment_sender, payment_proof, payment_proof_at) VALUES (?, ?, ?, ?, ?, ?, 'Menunggu Konfirmasi', ?, ?, ?, " . ($proofFile ? 'NOW()' : 'NULL') . ")");
    $paymentStatus = $proofFile ? 'Menunggu Verifikasi' : null;
    $stmt->execute([$invoice, $_SESSION['user_id'], $total, $paymentMethod, $buyerName, $buyerAddress . ' | HP: ' . $buyerPhone, $paymentStatus, $proofSender ? mb_substr($proofSender, 0, 100) : null, $proofFile]);
    $orderId = $pdo->lastInsertId();

    $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, book_id, title, price, quantity) VALUES (?, ?, ?, ?, ?)");
    $stockStmt = $pdo->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");

    foreach ($items as $it) {
        $itemStmt->execute([$orderId, $it['book_id'], $it['title'], $it['price'], $it['quantity']]);
        $stockStmt->execute([$it['quantity'], $it['book_id']]);
    }

    // Hapus dari keranjang hanya untuk checkout via keranjang (beli langsung tidak menyentuh keranjang)
    if (!$buyNow) {
        $delPlaceholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $pdo->prepare("DELETE FROM cart WHERE id IN ($delPlaceholders) AND user_id = ?")
            ->execute(array_merge($selectedIds, [$_SESSION['user_id']]));
    }

    $pdo->commit();
    unset($_SESSION['checkout_cart_ids'], $_SESSION['buy_now']);
} catch (Exception $e) {
    $pdo->rollBack();
    deleteProofImage($proofFile, $proofDir);
    flash('error', 'Transaksi gagal, silakan coba lagi.');
    header('Location: ' . $backUrl);
    exit;
}

if ($proofFile) {
    flash('success', 'Pesanan dibuat dan bukti pembayaran terkirim. Admin akan memeriksanya.');
}
header('Location: receipt.php?order_id=' . $orderId);
exit;
