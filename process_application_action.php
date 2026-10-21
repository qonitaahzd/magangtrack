<?php
/**
 * MAGANGTRACK - Process Application Action Handler
 * Lokasi berkas: magangtrack/process_application_action.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ------------------------------------------------------------------
    // PROTEKSI CSRF TOKEN
    // ------------------------------------------------------------------
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
        $_SESSION['flash_error'] = "Sesi tidak valid atau permintaan kedaluwarsa.";
        header('Location: explore.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $action  = $_POST['action'] ?? 'add_application';

    $db = getDBConnection();

    // ------------------------------------------------------------------
    // AKSI 1: Update Status Lamaran (Applied / Interview Scheduled / Accepted / Rejected)
    // ------------------------------------------------------------------
    if ($action === 'update_status') {
        $app_id  = filter_var($_POST['application_id'] ?? 0, FILTER_VALIDATE_INT);
        $status  = $_POST['status'] ?? '';
        $allowed = ['Applied', 'Interview Scheduled', 'Accepted', 'Rejected'];

        if ($app_id && in_array($status, $allowed)) {
            try {
                // Validasi: Cegah perubahan status jika lamaran sudah diaktivasi menjadi magang
                $stmtCheckInt = $db->prepare("SELECT COUNT(*) FROM internships WHERE application_id = :app_id");
                $stmtCheckInt->execute([':app_id' => $app_id]);
                if ($stmtCheckInt->fetchColumn() > 0) {
                    $_SESSION['flash_error'] = "Status lamaran tidak dapat diubah karena sudah diaktivasi menjadi program magang resmi.";
                    header('Location: applications.php');
                    exit();
                }

                // Update status lamaran sesuai kepemilikan user
                $stmtUp = $db->prepare("UPDATE applications SET status = :status WHERE id = :id AND user_id = :user_id");
                $stmtUp->execute([
                    ':status'  => $status,
                    ':id'      => $app_id,
                    ':user_id' => $user_id
                ]);

                $_SESSION['flash_success'] = "Status lamaran berhasil diperbarui.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat memperbarui status lamaran.";
            }
        } else {
            $_SESSION['flash_error'] = "Data lamaran atau pilihan status tidak valid.";
        }
        header('Location: applications.php');
        exit();

    // ------------------------------------------------------------------
    // AKSI 2: Hapus Lamaran dari Tracker
    // ------------------------------------------------------------------
    } elseif ($action === 'delete') {
        $app_id = filter_var($_POST['application_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($app_id) {
            try {
                // Validasi 1: Cegah penghapusan jika lamaran sudah terhubung dengan data magang
                $stmtCheckInt = $db->prepare("SELECT COUNT(*) FROM internships WHERE application_id = :app_id");
                $stmtCheckInt->execute([':app_id' => $app_id]);
                if ($stmtCheckInt->fetchColumn() > 0) {
                    $_SESSION['flash_error'] = "Lamaran tidak dapat dihapus karena sudah terhubung dengan program magang resmi.";
                    header('Location: applications.php');
                    exit();
                }

                // Validasi 2: Hapus lamaran sesuai kepemilikan user
                $stmtDel = $db->prepare("DELETE FROM applications WHERE id = :id AND user_id = :user_id");
                $stmtDel->execute([
                    ':id'      => $app_id,
                    ':user_id' => $user_id
                ]);

                $_SESSION['flash_success'] = "Data lamaran berhasil dihapus.";
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menghapus data lamaran.";
            }
        } else {
            $_SESSION['flash_error'] = "Data lamaran tidak ditemukan.";
        }
        header('Location: applications.php');
        exit();

    // ------------------------------------------------------------------
    // AKSI 3: Aktivasi Magang Resmi
    // ------------------------------------------------------------------
    } elseif ($action === 'activate_internship') {
        $app_id      = filter_var($_POST['application_id'] ?? 0, FILTER_VALIDATE_INT);
        $dosen_id    = filter_var($_POST['dosen_id'] ?? 0, FILTER_VALIDATE_INT);
        $tgl_mulai   = trim($_POST['tgl_mulai'] ?? '');
        $tgl_selesai = trim($_POST['tgl_selesai'] ?? '');

        // 1. Validasi Format Tanggal YYYY-MM-DD
        $timeMulai   = strtotime($tgl_mulai);
        $timeSelesai = strtotime($tgl_selesai);
        $todayStr    = date('Y-m-d');

        if (!$timeMulai || !$timeSelesai || date('Y-m-d', $timeMulai) !== $tgl_mulai || date('Y-m-d', $timeSelesai) !== $tgl_selesai) {
            $_SESSION['flash_error'] = "Format tanggal tidak valid.";
            header('Location: applications.php');
            exit();
        }

        // 2. Validasi Periode Magang
        if ($timeSelesai <= $timeMulai) {
            $_SESSION['flash_error'] = "Tanggal selesai magang harus lebih besar dari tanggal mulai.";
            header('Location: applications.php');
            exit();
        }

        if ($tgl_selesai < $todayStr) {
            $_SESSION['flash_error'] = "Periode magang sudah berakhir. Tanggal selesai harus hari ini atau di masa depan.";
            header('Location: applications.php');
            exit();
        }

        try {
            // 3. Validasi Dosen Pembimbing (Role Dosen & Terdaftar)
            $stmtDosenVer = $db->prepare("SELECT id FROM users WHERE id = :dosen_id AND role = 'dosen' LIMIT 1");
            $stmtDosenVer->execute([':dosen_id' => $dosen_id]);
            if (!$stmtDosenVer->fetch()) {
                $_SESSION['flash_error'] = "Dosen pembimbing yang dipilih tidak valid atau tidak terdaftar.";
                header('Location: applications.php');
                exit();
            }

            // 4. Validasi Batas Maksimal: 1 magang berstatus Aktif/Terjadwal per mahasiswa
            $stmtCheck = $db->prepare("SELECT COUNT(*) FROM internships WHERE user_id = :user_id AND status_magang IN ('Terjadwal', 'Aktif')");
            $stmtCheck->execute([':user_id' => $user_id]);
            if ($stmtCheck->fetchColumn() > 0) {
                $_SESSION['flash_error'] = "Anda sudah memiliki program magang yang sedang berjalan atau terdaftar.";
                header('Location: applications.php');
                exit();
            }

            // 5. Validasi Kepemilikan & Status Lamaran: Wajib 'Accepted' dan milik user bersangkutan
            $stmtAppVer = $db->prepare("SELECT id FROM applications WHERE id = :id AND user_id = :user_id AND status = 'Accepted'");
            $stmtAppVer->execute([':id' => $app_id, ':user_id' => $user_id]);
            if (!$stmtAppVer->fetch()) {
                $_SESSION['flash_error'] = "Aktivasi gagal. Lamaran tidak ditemukan atau belum diterima (Accepted).";
                header('Location: applications.php');
                exit();
            }

            // Simpan Data Magang Baru
            $status_magang = ($tgl_mulai <= $todayStr) ? 'Aktif' : 'Terjadwal';

            $stmtInsertInt = $db->prepare("
                INSERT INTO internships (user_id, application_id, dosen_id, tgl_mulai, tgl_selesai, status_magang)
                VALUES (:user_id, :app_id, :dosen_id, :mulai, :selesai, :status)
            ");
            $stmtInsertInt->execute([
                ':user_id'  => $user_id,
                ':app_id'   => $app_id,
                ':dosen_id' => $dosen_id,
                ':mulai'    => $tgl_mulai,
                ':selesai'  => $tgl_selesai,
                ':status'   => $status_magang
            ]);

            $_SESSION['flash_success'] = "Magang berhasil diaktivasi dengan status: {$status_magang}!";
            header('Location: applications.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat melakukan aktivasi magang.";
            header('Location: applications.php');
            exit();
        }

    // ------------------------------------------------------------------
    // AKSI 4: Penambahan Lamaran Baru dari Explore Internship (Default)
    // ------------------------------------------------------------------
    } else {
        $company_id    = filter_var($_POST['company_id'] ?? '', FILTER_VALIDATE_INT);
        $posisi_magang = trim($_POST['posisi_magang'] ?? '');
        $tgl_apply     = trim($_POST['tgl_apply'] ?? date('Y-m-d'));
        $catatan       = trim($_POST['catatan'] ?? '');

        if (!$company_id || empty($posisi_magang) || empty($tgl_apply)) {
            $_SESSION['flash_error'] = "Mohon pilih perusahaan dan isi posisi magang dengan benar.";
            header('Location: explore.php');
            exit();
        }

        try {
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
            header('Location: applications.php');
            exit();
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Terjadi kesalahan sistem saat menyimpan data lamaran.";
            header('Location: explore.php');
            exit();
        }
    }
}

// Redirect fallback jika diakses secara langsung tanpa POST
header('Location: explore.php');
exit();