<?php
require_once __DIR__ . '/auth/auth_middleware.php';
redirectIfLoggedIn();

$page_title = 'Login Pengguna';
include __DIR__ . '/includes/header.php';

$error   = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error'], $_SESSION['success']);
?>

<div class="auth-container">
    <div class="auth-header">
        <div class="auth-brand">
            <span class="auth-brand-dot"></span> MAGANGTRACK
        </div>
        <h1 class="auth-title">Selamat Datang</h1>
        <p class="auth-subtitle">Masuk untuk mengelola seluruh proses magang Anda</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form action="auth/login_process.php" method="POST">
        <div class="form-group">
            <label class="form-label" for="email">Email Kampus / Akun</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="nama@mahasiswa.ac.id" required autofocus>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Masuk ke Sistem</button>
    </form>

    <div class="auth-footer">
        Belum memiliki akun mahasiswa? <a href="register.php">Daftar Sekarang</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>