<?php
require_once __DIR__ . '/../auth/auth_middleware.php';
requireRole('dosen');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dosen Pembimbing - MAGANGTRACK</title>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; padding: 40px; background: #F8FAFC; }
        .card { background: #FFF; padding: 24px; border-radius: 12px; border: 1px solid #E2E8F0; max-width: 600px; }
        .btn-logout { display: inline-block; padding: 8px 16px; background: #EF4444; color: #FFF; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Panel Dosen Pembimbing - <?= htmlspecialchars($_SESSION['nama']); ?> 👨‍🏫</h2>
        <p>Anda terautentikasi sebagai <strong>Dosen Pembimbing</strong> (NIDN: <?= htmlspecialchars($_SESSION['nim_nidn']); ?>).</p>
        <p>Email: <?= htmlspecialchars($_SESSION['email']); ?></p>
        <a href="../logout.php" class="btn-logout">Logout</a>
    </div>
</body>
</html>