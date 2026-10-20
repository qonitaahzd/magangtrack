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

    $user_id       = $_SESSION['user_id'];
    $company_id    = filter_var($_POST['company_id'] ?? '', FILTER_VALIDATE_INT);
    $posisi_magang = trim($_POST['posisi_magang'] ?? '');
    $tgl_apply     = trim($_POST['tgl_apply'] ?? date('Y-m-d'));
    $catatan       = trim($_POST['catatan'] ?? '');

    // Validasi Server-Side Input Wajib
    if (!$company_id || empty($posisi_magang) || empty($tgl_apply)) {
        $_SESSION['flash_error'] = "Mohon pilih perusahaan dan isi posisi magang dengan benar.";
        header('Location: explore.php');
        exit();
    }

    try {
        $db = getDBConnection();
        
        // Verifikasi Otorisasi Perusahaan (Publik ATAU Mandiri Milik User Sendiri)
        $stmtComp = $db->prepare("
            SELECT nama_perusahaan 
            FROM companies 
            WHERE id = :id AND (is_verified = 1 OR created_by_user_id = :user_id)
        ");
        $stmtComp->execute([
            ':id'      => $company_id,
            ':user_id' => $user_id
        ]);
        $comp = $stmtComp->fetch();

        if (!$comp) {
            $_SESSION['flash_error'] = "Perusahaan tidak ditemukan atau Anda tidak memiliki akses.";
            header('Location: explore.php');
            exit();
        }

        // Insert Lamaran Baru
        $stmtInsert = $db->prepare("
            INSERT INTO applications (user_id, company_id, nama_perusahaan_snapshot, posisi_magang, status, tgl_apply, catatan)
            VALUES (:user_id, :company_id, :snapshot, :posisi, 'Applied', :tgl, :catatan)
        ");
        $stmtInsert->execute([
            ':user_id'    => $user_id,
            ':company_id' => $company_id,
            ':snapshot'   => $comp['nama_perusahaan'],
            ':posisi'     => $posisi_magang,
            ':tgl'        => $tgl_apply,
            ':catatan'    => $catatan ?: null
        ]);

        $_SESSION['flash_success'] = "Lamaran ke \"{$comp['nama_perusahaan']}\" berhasil ditambahkan ke Tracker!";
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menyimpan data lamaran.";
    }
}

// Sementara dialihkan ke explore.php sampai applications.php siap
header('Location: explore.php');
exit();