<?php
require_once '../config.php';
require_once '../includes/functions.php';

if (isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password']) && $user['role'] === 'admin') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        } elseif ($user && password_verify($password, $user['password'])) {
            $error = 'Akun ini bukan akun admin. Silakan login lewat halaman toko.';
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - FISTORE</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background:var(--footer-bg);min-height:100vh;">
<div class="container">
  <div class="auth-wrap" style="margin-top:80px;">
    <h1>FI<span style="color:var(--pink-darker);">STORE</span> Admin</h1>
    <p class="sub">Khusus untuk pengelola toko</p>

    <div class="demo-box">
      Akun demo admin: admin@fistore.com / admin123<br>
      (jalankan <code>setup.php</code> dulu jika akun belum ada)
    </div>

    <?php if ($error): ?><div class="flash flash-error" style="margin:0 0 16px;"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email Admin</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Log in sebagai Admin</button>
    </form>

    <div class="auth-switch"><a href="../index.php">&larr; Kembali ke halaman toko</a></div>
  </div>
</div>
</body>
</html>
