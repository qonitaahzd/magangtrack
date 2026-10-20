<?php
require_once __DIR__ . '/auth/auth_middleware.php';
redirectIfLoggedIn();

$page_title = 'Registrasi Mahasiswa';
include __DIR__ . '/includes/header.php';

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>

<div class="auth-container">
    <div class="auth-header">
        <div class="auth-brand">
            <span class="auth-brand-dot"></span> MAGANGTRACK
        </div>
        <h1 class="auth-title">Registrasi Akun</h1>
        <p class="auth-subtitle">Pendaftaran khusus untuk Mahasiswa</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">⚠️ <?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="auth/register_process.php" method="POST">
        <div class="form-group">
            <label class="form-label" for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" class="form-input" placeholder="Qonita Husna Zahida" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="nim">NIM (Nomor Induk Mahasiswa)</label>
            <input type="text" id="nim" name="nim" class="form-input" placeholder="I43252605" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Email Kampus</label>
            <input type="email" id="email" name="email" class="form-input" placeholder="qonita@mahasiswa.ac.id" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="prodi">Program Studi</label>
            <input type="text" id="prodi" name="prodi" class="form-input" value="Teknik Informatika" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="confirm_password">Konfirmasi Kata Sandi</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Daftar Akun Mahasiswa</button>
    </form>

    <div class="auth-footer">
        Sudah memiliki akun? <a href="login.php">Masuk di sini</a>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>