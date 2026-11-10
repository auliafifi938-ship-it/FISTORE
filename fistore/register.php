<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle = 'Daftar';
$hideNav = true; // halaman auth: tanpa navbar & footer

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah terdaftar. Silakan login.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Semua pendaftaran lewat form ini otomatis menjadi akun "user".
            // Akun admin hanya dibuat lewat setup.php demi keamanan.
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$name, $email, $hash]);

            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'user';
            flash('success', 'Pendaftaran berhasil! Selamat datang, ' . $name . '.');
            header('Location: index.php');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>
<div class="container">
  <div class="auth-wrap">
    <h1>Buat Akun</h1>
    <p class="sub">Daftar untuk mulai belanja di FISTORE &#128214;</p>

    <?php if ($error): ?><div class="flash flash-error" style="margin:0 0 16px;"><?= e($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="name" required value="<?= e($name) ?>">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?= e($email) ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
        <div class="form-hint">Minimal 6 karakter</div>
      </div>
      <div class="form-group">
        <label>Konfirmasi Password</label>
        <input type="password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Daftar</button>
    </form>

    <div class="auth-switch">Sudah punya akun? <a href="login.php">Log in di sini</a></div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
