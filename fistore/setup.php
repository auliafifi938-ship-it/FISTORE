<?php
require_once 'config.php';
require_once 'includes/functions.php';

$results = [];

function createAccountIfMissing($pdo, $name, $email, $password, $role, &$results) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $results[] = "Akun $email sudah ada, dilewati.";
        return;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hash, $role]);
    $results[] = "Akun $email ($role) berhasil dibuat.";
}

createAccountIfMissing($pdo, 'Admin FISTORE', 'admin@fistore.com', 'admin123', 'admin', $results);
createAccountIfMissing($pdo, 'Budi Santoso', 'user@fistore.com', 'user123', 'user', $results);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Setup FISTORE</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="padding:40px;">
<div class="auth-wrap">
  <h1>Setup Akun Demo</h1>
  <p class="sub">Jalankan sekali saja setelah import database.</p>
  <ul style="margin:16px 0; padding-left:18px; font-size:14px; line-height:1.8;">
    <?php foreach ($results as $r): ?>
      <li><?= e($r) ?></li>
    <?php endforeach; ?>
  </ul>
  <div class="demo-box">
    Admin &mdash; admin@fistore.com / admin123<br>
    User &mdash; user@fistore.com / user123
  </div>
  <a href="login.php" class="btn btn-primary btn-block">Ke Halaman Login</a>
  <p class="form-hint mt-20 text-center">Demi keamanan, hapus atau lindungi file setup.php ini setelah digunakan.</p>
</div>
</body>
</html>
