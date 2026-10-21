<?php
/**
 * MAGANGTRACK - Backend Action Handler Review Logbook Dosen
 * Lokasi berkas: magangtrack/process_dosen_logbook.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('dosen');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ------------------------------------------------------------------
    // 1. PROTEKSI CSRF TOKEN
    // ------------------------------------------------------------------
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
        $_SESSION['flash_error'] = "Sesi tidak valid atau permintaan kedaluwarsa.";
        header('Location: dosen_logbook.php');
        exit();
    }

    $db = getDBConnection();
    $dosen_id           = $_SESSION['user_id'];
    $logbook_id         = filter_var($_POST['logbook_id'] ?? 0, FILTER_VALIDATE_INT);
    $status_persetujuan = $_POST['status_persetujuan'] ?? '';
    $catatan_dosen      = trim($_POST['catatan_dosen'] ?? '');

    $allowedStatus = ['Approved', 'Revision Required'];

    if ($logbook_id && in_array($status_persetujuan, $allowedStatus)) {
        try {
            // ------------------------------------------------------------------
            // 2. OTORISASI KETAT: DOSEN HANYA BISA REVIEW LOGBOOK 'SUBMITTED'
            //    MILIK MAHASISWA BIMBINGANNYA SENDIRI
            // ------------------------------------------------------------------
            $stmtVer = $db->prepare("
                SELECT l.id, u.nama AS nama_mahasiswa
                FROM logbooks l
                JOIN internships i ON l.internship_id = i.id
                JOIN users u ON i.user_id = u.id
                WHERE l.id = :id AND i.dosen_id = :dosen_id AND l.status_persetujuan = 'Submitted'
            ");
            $stmtVer->execute([
                ':id'       => $logbook_id,
                ':dosen_id' => $dosen_id
            ]);
            $logbookMatch = $stmtVer->fetch();

            if ($logbookMatch) {
                // Update Status dan Catatan Dosen
                $stmtUp = $db->prepare("
                    UPDATE logbooks 
                    SET status_persetujuan = :status, catatan_dosen = :catatan 
                    WHERE id = :id
                ");
                $stmtUp->execute([
                    ':status'  => $status_persetujuan,
                    ':catatan' => !empty($catatan_dosen) ? $catatan_dosen : null,
                    ':id'      => $logbook_id
                ]);

                $mhsName = htmlspecialchars($logbookMatch['nama_mahasiswa']);
                $_SESSION['flash_success'] = "Logbook {$mhsName} berhasil diperbarui menjadi status '{$status_persetujuan}'.";
            } else {
                $_SESSION['flash_error'] = "Gagal memproses. Logbook tidak ditemukan, sudah pernah direview, atau berada di luar wewenang bimbingan Anda.";
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menyimpan persetujuan logbook.";
        }
    } else {
        $_SESSION['flash_error'] = "Pilihan status persetujuan tidak valid.";
    }
}

header('Location: dosen_logbook.php');
exit();