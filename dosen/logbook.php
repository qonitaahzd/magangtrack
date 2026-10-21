<?php
/**
 * MAGANGTRACK - Review Logbook (Dosen Pembimbing)
 * Lokasi berkas: magangtrack/dosen_logbook.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('dosen');

$db = getDBConnection();
$dosen_id = $_SESSION['user_id'];
$csrf_token = generateCSRFToken();

$filter_intern_id = filter_var($_GET['internship_id'] ?? 0, FILTER_VALIDATE_INT);

// ------------------------------------------------------------------
// 1. FETCH LOGBOOK MAHASISWA BIMBINGAN (PRIORITAS: SUBMITTED TERLEBIH DAHULU)
// ------------------------------------------------------------------
$query = "
    SELECT l.*, 
           u.nama AS nama_mahasiswa, 
           u.nim_nidn AS nim,
           a.nama_perusahaan_snapshot,
           a.posisi_magang
    FROM logbooks l
    JOIN internships i ON l.internship_id = i.id
    JOIN users u ON i.user_id = u.id
    JOIN applications a ON i.application_id = a.id
    WHERE i.dosen_id = :dosen_id
";
$params = [':dosen_id' => $dosen_id];

if ($filter_intern_id) {
    $query .= " AND i.id = :intern_id";
    $params[':intern_id'] = $filter_intern_id;
}

$query .= " ORDER BY CASE WHEN l.status_persetujuan = 'Submitted' THEN 1 ELSE 2 END, l.tanggal DESC, l.created_at DESC";

$stmtLogs = $db->prepare($query);
$stmtLogs->execute($params);
$logbooks = $stmtLogs->fetchAll();

// Fetch Daftar Mahasiswa Bimbingan untuk Opsi Filter Dropdown
$stmtStudentsList = $db->prepare("
    SELECT i.id AS internship_id, u.nama AS nama_mahasiswa, a.nama_perusahaan_snapshot
    FROM internships i
    JOIN users u ON i.user_id = u.id
    JOIN applications a ON i.application_id = a.id
    WHERE i.dosen_id = :dosen_id
    ORDER BY u.nama ASC
");
$stmtStudentsList->execute([':dosen_id' => $dosen_id]);
$studentsList = $stmtStudentsList->fetchAll();

// Session Flash Messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Review Logbook Harian Mahasiswa</h2>
        <a href="dosen/dashboard.php" class="text-xs text-brand-600 hover:text-brand-700 font-semibold flex items-center gap-1 transition-colors">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Kembali ke Dashboard Dosen</span>
        </a>
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

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Review & Persetujuan Logbook</h2>
                <p class="text-xs text-slate-500 mt-0.5">Periksa aktivitas harian, tentukan status persetujuan, dan berikan feedback untuk mahasiswa bimbingan Anda.</p>
            </div>

            <!-- Filter Mahasiswa -->
            <form method="GET" action="dosen_logbook.php" class="flex items-center gap-2">
                <select name="internship_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-surface-border rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-brand-500 cursor-pointer shadow-xs">
                    <option value="">-- Tampilkan Semua Mahasiswa --</option>
                    <?php foreach ($studentsList as $sl): ?>
                        <option value="<?= $sl['internship_id']; ?>" <?= $filter_intern_id == $sl['internship_id'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($sl['nama_mahasiswa']); ?> (<?= htmlspecialchars($sl['nama_perusahaan_snapshot']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($filter_intern_id): ?>
                    <a href="dosen_logbook.php" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors" title="Reset Filter">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-surface-border p-5 overflow-x-auto shadow-xs">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase text-[10px]">
                        <th class="py-3 px-3">Mahasiswa</th>
                        <th class="py-3 px-3">Tanggal & Durasi</th>
                        <th class="py-3 px-3">Uraian Kegiatan</th>
                        <th class="py-3 px-3">Status Logbook</th>
                        <th class="py-3 px-3">Form Feedback & Aksi Persetujuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($logbooks) > 0): ?>
                        <?php foreach ($logbooks as $log): ?>
                            <tr class="<?= $log['status_persetujuan'] === 'Submitted' ? 'bg-amber-50/30' : ''; ?>">
                                <td class="py-3.5 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                    <?= htmlspecialchars($log['nama_mahasiswa']); ?>
                                    <span class="block text-[10px] text-slate-400 font-mono font-normal">NIM: <?= htmlspecialchars($log['nim']); ?></span>
                                    <span class="block text-[10px] text-brand-600 font-normal"><?= htmlspecialchars($log['nama_perusahaan_snapshot']); ?></span>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800 block"><?= date('d M Y', strtotime($log['tanggal'])); ?></span>
                                    <span class="font-mono font-bold text-slate-700 text-[11px]"><?= htmlspecialchars($log['durasi_jam']); ?> Jam</span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-700 max-w-xs leading-relaxed">
                                    <?= nl2br(htmlspecialchars($log['kegiatan'])); ?>
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <?php
                                    $status = $log['status_persetujuan'];
                                    $badgeStyle = 'bg-slate-100 text-slate-600 border-slate-200';
                                    if ($status === 'Submitted') {
                                        $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-200 font-bold';
                                    } elseif ($status === 'Approved') {
                                        $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    } elseif ($status === 'Revision Required') {
                                        $badgeStyle = 'bg-rose-50 text-rose-700 border-rose-200';
                                    }
                                    ?>
                                    <span class="inline-block px-2.5 py-1 rounded-md text-[10px] font-semibold border <?= $badgeStyle; ?>">
                                        <?= htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 min-w-[280px]">
                                    <?php if ($log['status_persetujuan'] === 'Submitted'): ?>
                                        <form action="process_dosen_logbook.php" method="POST" class="space-y-2">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                                            <input type="hidden" name="logbook_id" value="<?= $log['id']; ?>">
                                            <input type="text" name="catatan_dosen" value="<?= htmlspecialchars($log['catatan_dosen'] ?? ''); ?>" placeholder="Tuliskan feedback / instruksi revisi..." class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-brand-500">
                                            <div class="flex gap-2">
                                                <button type="submit" name="status_persetujuan" value="Approved" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-semibold transition-colors flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </button>
                                                <button type="submit" name="status_persetujuan" value="Revision Required" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[10px] font-semibold transition-colors flex items-center gap-1">
                                                    <i class="fa-solid fa-rotate-left"></i> Minta Revisi
                                                </button>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="text-xs text-slate-600 space-y-1">
                                            <p class="italic text-slate-500">Catatan: <?= !empty($log['catatan_dosen']) ? htmlspecialchars($log['catatan_dosen']) : '-'; ?></p>
                                            <span class="text-[10px] text-slate-400 block"><i class="fa-solid fa-check-double mr-1"></i>Sudah Direview</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada logbook yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>