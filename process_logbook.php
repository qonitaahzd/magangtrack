<?php
/**
 * MAGANGTRACK - Backend Action Handler Digital Logbook
 * Lokasi berkas: magangtrack/process_logbook.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ------------------------------------------------------------------
    // 1. PROTEKSI CSRF TOKEN
    // ------------------------------------------------------------------
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
        $_SESSION['flash_error'] = "Sesi tidak valid atau permintaan kedaluwarsa.";
        header('Location: logbook.php');
        exit();
    }

    $db = getDBConnection();
    $user_id = $_SESSION['user_id'];
    $action  = $_POST['action'] ?? '';

    // ------------------------------------------------------------------
    // 2. KEAMANAN BACKEND: CEK KEPEMILIKAN MAGANG BERSTATUS AKTIF
    // ------------------------------------------------------------------
    $stmtCheckActive = $db->prepare("
        SELECT id, tgl_mulai, tgl_selesai 
        FROM internships 
        WHERE user_id = :user_id AND status_magang = 'Aktif' 
        LIMIT 1
    ");
    $stmtCheckActive->execute([':user_id' => $user_id]);
    $activeInternship = $stmtCheckActive->fetch();

    if (!$activeInternship) {
        $_SESSION['flash_error'] = "Aksi ditolak. Fitur logbook hanya dapat digunakan saat status magang Anda berstatus Aktif.";
        header('Location: dashboard.php');
        exit();
    }

    $internship_id = $activeInternship['id'];
    $tgl_mulai     = $activeInternship['tgl_mulai'];
    $tgl_selesai   = $activeInternship['tgl_selesai'];

    // ------------------------------------------------------------------
    // AKSI 1: TAMBAH LOGBOOK BARU (DRAFT)
    // ------------------------------------------------------------------
    if ($action === 'create') {
        $tanggal    = trim($_POST['tanggal'] ?? '');
        $kegiatan   = trim($_POST['kegiatan'] ?? '');
        $durasi_jam = filter_var($_POST['durasi_jam'] ?? 0, FILTER_VALIDATE_INT);

        // Validasi Durasi (1 - 24 Jam)
        if ($durasi_jam < 1 || $durasi_jam > 24) {
            $_SESSION['flash_error'] = "Durasi jam magang harus di antara 1 hingga 24 jam.";
            header('Location: logbook.php');
            exit();
        }

        // Validasi Tanggal Kegiatan dalam Periode Magang
        if (empty($tanggal) || $tanggal < $tgl_mulai || $tanggal > $tgl_selesai) {
            $_SESSION['flash_error'] = "Tanggal kegiatan harus berada dalam periode magang (" . date('d M Y', strtotime($tgl_mulai)) . " s.d. " . date('d M Y', strtotime($tgl_selesai)) . ").";
            header('Location: logbook.php');
            exit();
        }

        if (!empty($kegiatan)) {
            try {
                $stmtInsert = $db->prepare("
                    INSERT INTO logbooks (internship_id, tanggal, kegiatan, durasi_jam, status_persetujuan)
                    VALUES (:intern_id, :tanggal, :kegiatan, :durasi, 'Draft')
                ");
                $stmtInsert->execute([
                    ':intern_id' => $internship_id,
                    ':tanggal'   => $tanggal,
                    ':kegiatan'  => $kegiatan,
                    ':durasi'    => $durasi_jam
                ]);

                $_SESSION['flash_success'] = "Logbook harian berhasil disimpan sebagai Draft.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menyimpan logbook.";
            }
        } else {
            $_SESSION['flash_error'] = "Uraian kegiatan magang wajib diisi.";
        }

    // ------------------------------------------------------------------
    // AKSI 2: EDIT LOGBOOK (TERKUNCI UNTUK SUBMITTED & APPROVED)
    // ------------------------------------------------------------------
    } elseif ($action === 'update') {
        $logbook_id = filter_var($_POST['logbook_id'] ?? 0, FILTER_VALIDATE_INT);
        $tanggal    = trim($_POST['tanggal'] ?? '');
        $kegiatan   = trim($_POST['kegiatan'] ?? '');
        $durasi_jam = filter_var($_POST['durasi_jam'] ?? 0, FILTER_VALIDATE_INT);

        // Validasi Durasi (1 - 24 Jam)
        if ($durasi_jam < 1 || $durasi_jam > 24) {
            $_SESSION['flash_error'] = "Durasi jam magang harus di antara 1 hingga 24 jam.";
            header('Location: logbook.php');
            exit();
        }

        // Validasi Tanggal Kegiatan dalam Periode Magang
        if (empty($tanggal) || $tanggal < $tgl_mulai || $tanggal > $tgl_selesai) {
            $_SESSION['flash_error'] = "Tanggal kegiatan harus berada dalam periode magang (" . date('d M Y', strtotime($tgl_mulai)) . " s.d. " . date('d M Y', strtotime($tgl_selesai)) . ").";
            header('Location: logbook.php');
            exit();
        }

        if ($logbook_id && !empty($kegiatan)) {
            try {
                // Keamanan Backend: Verifikasi kepemilikan dan status logbook
                $stmtVer = $db->prepare("
                    SELECT id, status_persetujuan 
                    FROM logbooks 
                    WHERE id = :id AND internship_id = :intern_id
                ");
                $stmtVer->execute([':id' => $logbook_id, ':intern_id' => $internship_id]);
                $logData = $stmtVer->fetch();

                if ($logData) {
                    // Kunci tegas untuk status Submitted dan Approved
                    if (in_array($logData['status_persetujuan'], ['Submitted', 'Approved'])) {
                        $_SESSION['flash_error'] = "Logbook dengan status '" . $logData['status_persetujuan'] . "' terkunci dan tidak dapat diedit.";
                    } else {
                        // Hanya status Draft dan Revision Required yang boleh diedit (kembali ke Draft)
                        $stmtUp = $db->prepare("
                            UPDATE logbooks 
                            SET tanggal = :tanggal, kegiatan = :kegiatan, durasi_jam = :durasi, status_persetujuan = 'Draft'
                            WHERE id = :id AND internship_id = :intern_id AND status_persetujuan IN ('Draft', 'Revision Required')
                        ");
                        $stmtUp->execute([
                            ':tanggal'   => $tanggal,
                            ':kegiatan'  => $kegiatan,
                            ':durasi'    => $durasi_jam,
                            ':id'        => $logbook_id,
                            ':intern_id' => $internship_id
                        ]);

                        $_SESSION['flash_success'] = "Logbook harian berhasil diperbarui.";
                    }
                } else {
                    $_SESSION['flash_error'] = "Data logbook tidak ditemukan atau di luar wewenang Anda.";
                }
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat memperbarui logbook.";
            }
        } else {
            $_SESSION['flash_error'] = "Uraian kegiatan magang wajib diisi.";
        }

    // ------------------------------------------------------------------
    // AKSI 3: SUBMIT LOGBOOK KE DOSEN PEMBIMBING
    // ------------------------------------------------------------------
    } elseif ($action === 'submit') {
        $logbook_id = filter_var($_POST['logbook_id'] ?? 0, FILTER_VALIDATE_INT);

        if ($logbook_id) {
            try {
                // Keamanan Backend: Hanya izinkan submit dari Draft
                $stmtSub = $db->prepare("
                    UPDATE logbooks 
                    SET status_persetujuan = 'Submitted'
                    WHERE id = :id AND internship_id = :intern_id AND status_persetujuan = 'Draft'
                ");
                $stmtSub->execute([':id' => $logbook_id, ':intern_id' => $internship_id]);

                if ($stmtSub->rowCount() > 0) {
                    $_SESSION['flash_success'] = "Logbook berhasil dikirimkan ke Dosen Pembimbing untuk direview.";
                } else {
                    $_SESSION['flash_error'] = "Gagal mengirim. Logbook sudah dikirimkan, disetujui, atau butuh revisi.";
                }
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat mengirim logbook.";
            }
        }

    // ------------------------------------------------------------------
    // AKSI 4: HAPUS LOGBOOK (TERKUNCI UNTUK SUBMITTED & APPROVED)
    // ------------------------------------------------------------------
    } elseif ($action === 'delete') {
        $logbook_id = filter_var($_POST['logbook_id'] ?? 0, FILTER_VALIDATE_INT);

        if ($logbook_id) {
            try {
                // Keamanan Backend: Melarang penghapusan logbook Submitted atau Approved
                $stmtDel = $db->prepare("
                    DELETE FROM logbooks 
                    WHERE id = :id AND internship_id = :intern_id AND status_persetujuan IN ('Draft', 'Revision Required')
                ");
                $stmtDel->execute([':id' => $logbook_id, ':intern_id' => $internship_id]);

                if ($stmtDel->rowCount() > 0) {
                    $_SESSION['flash_success'] = "Catatan logbook berhasil dihapus.";
                } else {
                    $_SESSION['flash_error'] = "Logbook yang telah dikirim atau disetujui terkunci dan tidak dapat dihapus.";
                }
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menghapus logbook.";
            }
        }
    }
}

header('Location: logbook.php');
exit();