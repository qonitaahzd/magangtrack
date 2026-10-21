<?php
/**
 * MAGANGTRACK - Backend Action Handler Document Checklist
 * Lokasi berkas: magangtrack/process_checklist.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF Token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
        $_SESSION['flash_error'] = "Sesi tidak valid atau permintaan kedaluwarsa.";
        header('Location: checklist.php');
        exit();
    }

    $db = getDBConnection();
    $user_id = $_SESSION['user_id'];
    $doc_id  = filter_var($_POST['doc_id'] ?? 0, FILTER_VALIDATE_INT);

    if ($doc_id) {
        try {
            // Verifikasi Otorisasi: Pastikan dokumen milik user yang sedang login
            $stmtVer = $db->prepare("SELECT id, status FROM document_checklists WHERE id = :id AND user_id = :user_id LIMIT 1");
            $stmtVer->execute([':id' => $doc_id, ':user_id' => $user_id]);
            $doc = $stmtVer->fetch();

            if ($doc) {
                $newStatus = ($doc['status'] === 'Lengkap') ? 'Belum Lengkap' : 'Lengkap';
                $stmtUp = $db->prepare("UPDATE document_checklists SET status = :status WHERE id = :id AND user_id = :user_id");
                $stmtUp->execute([':status' => $newStatus, ':id' => $doc_id, ':user_id' => $user_id]);

                $_SESSION['flash_success'] = "Status dokumen berhasil diperbarui.";
            } else {
                $_SESSION['flash_error'] = "Dokumen tidak ditemukan atau berada di luar wewenang Anda.";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat memperbarui dokumen.";
        }
    }
}

header('Location: checklist.php');
exit();