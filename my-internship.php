<?php
/**
 * MAGANGTRACK - My Internship Details & Overview
 * Lokasi berkas: magangtrack/my-internship.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// ------------------------------------------------------------------
// 1. FETCH DATA MAGANG MAHASISWA (BERSTATUS AKTIF ATAU TERJADWAL)
// ------------------------------------------------------------------
$stmtIntern = $db->prepare("
    SELECT i.*, 
           a.nama_perusahaan_snapshot, 
           a.posisi_magang,
           c.lokasi, 
           c.sektor_industri, 
           c.kontak_email AS email_perusahaan,
           d.nama AS nama_dosen, 
           d.email AS email_dosen, 
           d.nim_nidn AS nidn_dosen,
           (SELECT COALESCE(SUM(durasi_jam), 0) 
            FROM logbooks 
            WHERE internship_id = i.id AND status_persetujuan = 'Approved') AS total_approved_hours,
           (SELECT COUNT(*) 
            FROM logbooks 
            WHERE internship_id = i.id AND status_persetujuan = 'Submitted') AS pending_logbooks_count
    FROM internships i
    JOIN applications a ON i.application_id = a.id
    LEFT JOIN companies c ON a.company_id = c.id
    JOIN users d ON i.dosen_id = d.id
    WHERE i.user_id = :user_id AND i.status_magang IN ('Aktif', 'Terjadwal')
    ORDER BY i.created_at DESC
    LIMIT 1
");
$stmtIntern->execute([':user_id' => $user_id]);
$intern = $stmtIntern->fetch();

// Keamanan: Jika belum ada magang aktif/terjadwal (seperti Qonita), alihkan ke dashboard
if (!$intern) {
    $_SESSION['flash_error'] = "Anda belum memiliki program magang yang Aktif atau Terjadwal.";
    header('Location: dashboard.php');
    exit();
}

$isInternshipActive = ($intern['status_magang'] === 'Aktif');

// Hitung Estimasi Total Hari dan Progres Durasi Magang
$tgl_mulai   = strtotime($intern['tgl_mulai']);
$tgl_selesai = strtotime($intern['tgl_selesai']);
$today       = time();

$total_days = max(1, round(($tgl_selesai - $tgl_mulai) / (60 * 60 * 24)));
$passed_days = max(0, round(($today - $tgl_mulai) / (60 * 60 * 24)));
$progress_percent = $isInternshipActive ? min(100, max(0, round(($passed_days / $total_days) * 100))) : 0;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Detail Magang Saya</h2>
        <span class="text-xs text-slate-500"><?= htmlspecialchars($_SESSION['program_studi'] ?? ''); ?></span>
    </header>

    <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
        <div class="bg-white rounded-2xl border border-surface-border p-6 shadow-xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wide uppercase <?= $isInternshipActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'; ?>">
                        Status: <?= htmlspecialchars($intern['status_magang']); ?>
                    </span>
                    <h2 class="text-2xl font-bold text-slate-900 tracking-tight mt-2"><?= htmlspecialchars($intern['nama_perusahaan_snapshot']); ?></h2>
                    <p class="text-xs font-semibold text-brand-600 mt-0.5"><i class="fa-solid fa-briefcase mr-1.5"></i><?= htmlspecialchars($intern['posisi_magang']); ?></p>
                </div>

                <?php if ($isInternshipActive): ?>
                    <a href="logbook.php" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-colors shadow-xs">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>Buka Digital Logbook</span>
                    </a>
                <?php else: ?>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-medium max-w-xs text-center">
                        <i class="fa-solid fa-clock mr-1"></i> Magang Anda terdaftar sebagai <strong>Terjadwal</strong>. Logbook dapat diisi saat periode dimulainya magang.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Progres Bar Periode Magang -->
            <div class="space-y-1.5">
                <div class="flex justify-between text-xs font-semibold">
                    <span class="text-slate-600">Progres Timeline Magang</span>
                    <span class="text-brand-600 font-bold"><?= $progress_percent; ?>%</span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-600 rounded-full transition-all duration-500" style="width: <?= $progress_percent; ?>%;"></div>
                </div>
                <div class="flex justify-between text-[11px] text-slate-400 font-mono">
                    <span>Mulai: <?= date('d M Y', strtotime($intern['tgl_mulai'])); ?></span>
                    <span>Selesai: <?= date('d M Y', strtotime($intern['tgl_selesai'])); ?></span>
                </div>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-1">
                <span class="text-xs font-semibold text-slate-400">Total Jam Magang (Approved)</span>
                <p class="text-2xl font-bold text-brand-600 font-mono"><?= number_format($intern['total_approved_hours']); ?> <span class="text-xs font-normal text-slate-500">Jam</span></p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-1">
                <span class="text-xs font-semibold text-slate-400">Logbook Menunggu Review</span>
                <p class="text-2xl font-bold text-amber-600 font-mono"><?= number_format($intern['pending_logbooks_count']); ?> <span class="text-xs font-normal text-slate-500">Item</span></p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-1">
                <span class="text-xs font-semibold text-slate-400">Sektor Industri</span>
                <p class="text-base font-bold text-slate-800 truncate"><?= htmlspecialchars($intern['sektor_industri'] ?? 'Umum / Industri'); ?></p>
            </div>
        </div>

        <!-- Rincian Informasi -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-3 text-xs">
                <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <i class="fa-solid fa-building text-brand-600"></i> Informasi Tempat Magang
                </h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Lokasi / Alamat</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($intern['lokasi'] ?? 'Tidak dicantumkan'); ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Email Perusahaan</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($intern['email_perusahaan'] ?? 'Tidak dicantumkan'); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-3 text-xs">
                <h3 class="font-bold text-slate-900 border-b border-slate-100 pb-2.5 flex items-center gap-2">
                    <i class="fa-solid fa-user-tie text-brand-600"></i> Dosen Pembimbing Lapangan
                </h3>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Nama Dosen</span>
                        <span class="font-semibold text-slate-800"><?= htmlspecialchars($intern['nama_dosen']); ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">NIDN</span>
                        <span class="font-mono text-slate-700"><?= htmlspecialchars($intern['nidn_dosen']); ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Email Kontak</span>
                        <span class="font-medium text-slate-800"><?= htmlspecialchars($intern['email_dosen']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>