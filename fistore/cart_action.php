<?php
require_once 'config.php';
require_once 'includes/functions.php';
requireLogin();

$action = $_POST['action'] ?? '';
$cartId = (int)($_POST['cart_id'] ?? 0);

$stmt = $pdo->prepare("SELECT c.*, b.stock FROM cart c JOIN books b ON c.book_id = b.id WHERE c.id = ? AND c.user_id = ?");
$stmt->execute([$cartId, $_SESSION['user_id']]);
$item = $stmt->fetch();

if ($item) {
    if ($action === 'increase') {
        $newQty = min($item['stock'], $item['quantity'] + 1);
        $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?")->execute([$newQty, $cartId]);
    } elseif ($action === 'decrease') {
        $newQty = $item['quantity'] - 1;
        if ($newQty < 1) {
            $pdo->prepare("DELETE FROM cart WHERE id = ?")->execute([$cartId]);
        } else {
            $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?")->execute([$newQty, $cartId]);
        }
    } elseif ($action === 'remove') {
        $pdo->prepare("DELETE FROM cart WHERE id = ?")->execute([$cartId]);
    }
}

header('Location: cart.php');
exit;
