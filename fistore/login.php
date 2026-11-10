<?php
require_once 'config.php';
require_once 'includes/functions.php';
$pageTitle = 'Login';
$hideNav = true; // halaman auth: tanpa navbar & footer

if (isLoggedIn()) {
    header('Location: index.php');
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
            $error = 'Akun ini adalah akun admin. Silakan login lewat <a href="admin/login.php">halaman login admin</a>.';
        } elseif ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            flash('success', 'Selamat datang kembali, ' . $user['name'] . '!');
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

require_once 'includes/header.php';
?>
<div class="container">
  <div class="auth-wrap">
    <h1>Masuk ke FISTORE</h1>
    <p class="sub">Senang bertemu kamu lagi &#128151;</p>

    <div class="demo-box">
      <b>Akun demo user</b> (jalankan setup.php dulu jika belum ada):<br>
      user@fistore.com / user123<br>
      <span style="opacity:.8;">Admin? Login lewat <a href="admin/login.php">halaman admin</a>.</span>
    </div>

    <?php if ($error): ?><div class="flash flash-error" style="margin:0 0 16px;"><?= $error === strip_tags($error) ? e($error) : $error ?></div><?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>

    <div class="auth-switch">Belum punya akun? <a href="register.php">Daftar di sini</a></div>
  </div>
</div>
<?php require_once 'includes/footer.php'; ?>
