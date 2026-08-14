<?php
// login.php - autentikasi admin (B1)
require_once __DIR__ . '/partials.php';
require_once __DIR__ . '/koneksi.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = mysqli_prepare($koneksi, 'SELECT id, username, password_hash, nama_lengkap FROM admin WHERE username = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin']    = true;
        $_SESSION['username'] = $admin['username'];
        $_SESSION['nama']     = $admin['nama_lengkap'];
        header('Location: /admin/dashboard.php');
        exit;
    }
    $error = 'Username atau password salah.';
}

render_header('Login Admin');
?>
<div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock fs-1 text-primary"></i>
                    <h4 class="mt-2 mb-0">Login Admin</h4>
                    <p class="text-muted small mb-0">Masuk untuk mengelola kos</p>
                </div>
                <?php if ($error): ?>
                    <div class="alert alert-danger py-2"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" autocomplete="off">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input class="form-control" name="username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right"></i> Masuk</button>
                </form>
                <hr>
                <p class="text-muted small text-center mb-0">Demo: <code>admin</code> / <code>admin123</code></p>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>
