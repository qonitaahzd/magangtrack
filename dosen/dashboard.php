<?php
/**
 * MAGANGTRACK - Dashboard Dosen Pembimbing
 * Lokasi berkas: magangtrack/dosen/dashboard.php
 */

require_once __DIR__ . '/../auth/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';

requireRole('dosen');

$db = getDBConnection();
$dosen_id = $_SESSION['user_id'];

// ------------------------------------------------------------------
// 1. FETCH METRIK DAN DAFTAR MAHASISWA BIMBINGAN
// ------------------------------------------------------------------
$stmtStudents = $db->prepare("
    SELECT i.id AS internship_id,
           i.status_magang,
           i.tgl_mulai,
           i.tgl_selesai,
           u.id AS mhs_id,
           u.nama AS nama_mahasiswa,
           u.nim_nidn AS nim,
           u.program_studi,
           a.nama_perusahaan_snapshot,
           a.posisi_magang,
           (SELECT COUNT(*) 
            FROM logbooks 
            WHERE internship_id = i.id AND status_persetujuan = 'Submitted') AS pending_logbooks,
           (SELECT COALESCE(SUM(durasi_jam), 0) 
            FROM logbooks 
            WHERE internship_id = i.id AND status_persetujuan = 'Approved') AS approved_hours
    FROM internships i
    JOIN users u ON i.user_id = u.id
    JOIN applications a ON i.application_id = a.id
    WHERE i.dosen_id = :dosen_id
    ORDER BY pending_logbooks DESC, i.created_at DESC
");
$stmtStudents->execute([':dosen_id' => $dosen_id]);
$students = $stmtStudents->fetchAll();

// Metrik Ringkasan Dosen
$totalStudents = count($students);
$totalPendingReview = array_sum(array_column($students, 'pending_logbooks'));

// Session Flash Messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Portal Dosen Pembimbing</h2>
        <span class="text-xs text-slate-500 font-mono">NIDN: <?= htmlspecialchars($_SESSION['nim_nidn'] ?? ''); ?></span>
    </header>

    <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
        <?php if ($flashSuccess): ?>
            <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600"></i>
                <span><?= htmlspecialchars($flashSuccess); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                <span><?= htmlspecialchars($flashError); ?></span>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="bg-white rounded-2xl p-6 border border-surface-border flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?> 👋</h2>
                <p class="text-xs text-slate-500 mt-1">Pantau progres magang dan berikan persetujuan logbook harian mahasiswa bimbingan Anda.</p>
            </div>
            <a href="../dosen_logbook.php" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition-colors shadow-xs">
                <i class="fa-solid fa-book-check"></i>
                <span>Review Semua Logbook</span>
            </a>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-1">
                <span class="text-xs font-semibold text-slate-400">Total Mahasiswa Bimbingan</span>
                <p class="text-2xl font-bold text-slate-900 font-mono"><?= number_format($totalStudents); ?> <span class="text-xs font-normal text-slate-500">Mahasiswa</span></p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-1">
                <span class="text-xs font-semibold text-slate-400">Logbook Menunggu Review</span>
                <p class="text-2xl font-bold <?= $totalPendingReview > 0 ? 'text-rose-600' : 'text-emerald-600'; ?> font-mono">
                    <?= number_format($totalPendingReview); ?> <span class="text-xs font-normal text-slate-500">Item Pending</span>
                </p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-surface-border space-y-1">
                <span class="text-xs font-semibold text-slate-400">Status Pembimbingan</span>
                <p class="text-base font-bold text-emerald-600 flex items-center gap-1.5 mt-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Aktif Berjalan
                </p>
            </div>
        </div>

        <!-- Tabel Daftar Mahasiswa Bimbingan -->
        <div class="bg-white rounded-2xl border border-surface-border p-5 overflow-x-auto shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Daftar Mahasiswa & Progres Kegiatan</h3>
                <span class="text-[11px] text-slate-400 font-mono"><?= $totalStudents; ?> Mahasiswa Terdaftar</span>
            </div>

            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase text-[10px]">
                        <th class="py-3 px-3">Mahasiswa</th>
                        <th class="py-3 px-3">Perusahaan & Posisi</th>
                        <th class="py-3 px-3">Status Magang</th>
                        <th class="py-3 px-3">Progres Timeline</th>
                        <th class="py-3 px-3">Approved Hours</th>
                        <th class="py-3 px-3">Logbook Pending</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $st): ?>
                            <?php
                            // Hitung Progres % Timeline Magang
                            $tgl_mulai   = strtotime($st['tgl_mulai']);
                            $tgl_selesai = strtotime($st['tgl_selesai']);
                            $today       = time();
                            $total_days  = max(1, round(($tgl_selesai - $tgl_mulai) / (60 * 60 * 24)));
                            $passed_days = max(0, round(($today - $tgl_mulai) / (60 * 60 * 24)));
                            $progress_percent = ($st['status_magang'] === 'Aktif') ? min(100, max(0, round(($passed_days / $total_days) * 100))) : 0;
                            ?>
                            <tr>
                                <td class="py-3.5 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                    <?= htmlspecialchars($st['nama_mahasiswa']); ?>
                                    <span class="block text-[10px] text-slate-400 font-mono font-normal">NIM: <?= htmlspecialchars($st['nim']); ?></span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 max-w-xs">
                                    <span class="font-semibold text-slate-800 block truncate"><?= htmlspecialchars($st['nama_perusahaan_snapshot']); ?></span>
                                    <span class="text-[10px] text-slate-400 block truncate"><?= htmlspecialchars($st['posisi_magang']); ?></span>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-semibold border <?= $st['status_magang'] === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'; ?>">
                                        <?= htmlspecialchars($st['status_magang']); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 min-w-[120px]">
                                    <div class="flex justify-between text-[10px] font-semibold text-slate-500 mb-1">
                                        <span><?= $progress_percent; ?>%</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-600 rounded-full" style="width: <?= $progress_percent; ?>%;"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 font-mono font-bold text-brand-600 whitespace-nowrap">
                                    <?= number_format($st['approved_hours']); ?> Jam
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <?php if ($st['pending_logbooks'] > 0): ?>
                                        <span class="px-2.5 py-1 rounded-md bg-rose-50 border border-rose-200 text-rose-700 font-bold text-[10px]">
                                            <i class="fa-solid fa-clock text-[9px] mr-1"></i><?= $st['pending_logbooks']; ?> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[10px]">0 Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                    <a href="../dosen_logbook.php?internship_id=<?= $st['internship_id']; ?>" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-[11px] font-semibold transition-colors inline-flex items-center gap-1.5">
                                        <span>Review Logbook</span>
                                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada mahasiswa bimbingan yang terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>