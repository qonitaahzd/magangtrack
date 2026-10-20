<?php
require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';
requireRole('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
        $_SESSION['flash_error'] = "Sesi tidak valid atau permintaan kedaluwarsa.";
        header('Location: explore.php');
        exit();
    }

    $user_id         = $_SESSION['user_id'];
    $nama_perusahaan = trim($_POST['nama_perusahaan'] ?? '');
    $sektor_industri = trim($_POST['sektor_industri'] ?? '');
    $lokasi          = trim($_POST['lokasi'] ?? '');
    $kontak_email    = trim($_POST['kontak_email'] ?? '');

    // Validasi Server-Side Input Wajib
    if (empty($nama_perusahaan) || empty($sektor_industri) || empty($lokasi)) {
        $_SESSION['flash_error'] = "Mohon isi semua bidang formulir yang wajib diisi.";
        header('Location: explore.php');
        exit();
    }

    // Validasi Format Email
    if (!empty($kontak_email) && !filter_var($kontak_email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = "Format email kontak perusahaan tidak valid.";
        header('Location: explore.php');
        exit();
    }

    try {
        $db = getDBConnection();
        $stmt = $db->prepare("
            INSERT INTO companies (nama_perusahaan, sektor_industri, lokasi, kontak_email, is_verified, created_by_user_id) 
            VALUES (:nama, :sektor, :lokasi, :email, 0, :user_id)
        ");
        $stmt->execute([
            ':nama'    => $nama_perusahaan,
            ':sektor'  => $sektor_industri,
            ':lokasi'  => $lokasi,
            ':email'   => $kontak_email ?: null,
            ':user_id' => $user_id
        ]);

        $_SESSION['flash_success'] = "Perusahaan mandiri \"{$nama_perusahaan}\" berhasil ditambahkan!";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menyimpan data perusahaan.";
    }
}

header('Location: explore.php');
exit();