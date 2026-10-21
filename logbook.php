<?php
/**
 * MAGANGTRACK - Digital Logbook Mahasiswa
 * Lokasi berkas: magangtrack/logbook.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$csrf_token = generateCSRFToken();

// ------------------------------------------------------------------
// 1. KEAMANAN BACKEND: CEK KEPEMILIKAN MAGANG AKTIF
// ------------------------------------------------------------------
$stmtIntern = $db->prepare("
    SELECT i.id, i.status_magang, i.tgl_mulai, i.tgl_selesai, a.nama_perusahaan_snapshot, a.posisi_magang
    FROM internships i
    JOIN applications a ON i.application_id = a.id
    WHERE i.user_id = :user_id AND i.status_magang = 'Aktif'
    LIMIT 1
");
$stmtIntern->execute([':user_id' => $user_id]);
$activeIntern = $stmtIntern->fetch();

// Jika tidak ada magang Aktif (seperti Qonita atau Terjadwal), tolak akses
if (!$activeIntern) {
    $_SESSION['flash_error'] = "Fitur Digital Logbook hanya dapat digunakan saat status magang Anda berstatus Aktif.";
    header('Location: dashboard.php');
    exit();
}

$internship_id = $activeIntern['id'];

// Batasi input tanggal HTML agar tidak melebihi periode magang
$min_date = $activeIntern['tgl_mulai'];
$max_date = (date('Y-m-d') < $activeIntern['tgl_selesai']) ? date('Y-m-d') : $activeIntern['tgl_selesai'];

// ------------------------------------------------------------------
// 2. FETCH ITEM LOGBOOK MILIK MAHASISWA
// ------------------------------------------------------------------
$stmtLogs = $db->prepare("
    SELECT id, internship_id, tanggal, kegiatan, durasi_jam, status_persetujuan, catatan_dosen, created_at
    FROM logbooks 
    WHERE internship_id = :intern_id 
    ORDER BY tanggal DESC, created_at DESC
");
$stmtLogs->execute([':intern_id' => $internship_id]);
$logbooks = $stmtLogs->fetchAll();

// Hitung Total Jam Approved
$stmtHours = $db->prepare("
    SELECT COALESCE(SUM(durasi_jam), 0) 
    FROM logbooks 
    WHERE internship_id = :intern_id AND status_persetujuan = 'Approved'
");
$stmtHours->execute([':intern_id' => $internship_id]);
$totalApprovedHours = $stmtHours->fetchColumn();

// Session Flash Messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Digital Logbook Magang</h2>
        <span class="text-xs text-slate-500"><?= htmlspecialchars($_SESSION['program_studi'] ?? ''); ?></span>
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
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Catatan Logbook Harian</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    <?= htmlspecialchars($activeIntern['nama_perusahaan_snapshot']); ?> • <span class="font-semibold text-brand-600"><?= htmlspecialchars($activeIntern['posisi_magang']); ?></span>
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="bg-white border border-surface-border px-3.5 py-1.5 rounded-xl text-xs">
                    <span class="text-slate-400 block text-[9px] uppercase font-bold">Total Approved</span>
                    <span class="font-mono font-bold text-brand-600 text-sm"><?= number_format($totalApprovedHours); ?> Jam</span>
                </div>
                <button type="button" onclick="openAddModal()" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors shadow-xs">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tambah Logbook</span>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-surface-border p-5 overflow-x-auto shadow-xs">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase text-[10px]">
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Uraian Kegiatan</th>
                        <th class="py-3 px-3">Durasi</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Feedback Dosen</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($logbooks) > 0): ?>
                        <?php foreach ($logbooks as $log): ?>
                            <tr>
                                <td class="py-3.5 px-3 font-semibold text-slate-800 whitespace-nowrap">
                                    <?= date('d M Y', strtotime($log['tanggal'])); ?>
                                </td>
                                <td class="py-3.5 px-3 text-slate-700 max-w-xs leading-relaxed">
                                    <?= nl2br(htmlspecialchars($log['kegiatan'])); ?>
                                </td>
                                <td class="py-3.5 px-3 font-mono font-bold text-slate-800 whitespace-nowrap">
                                    <?= htmlspecialchars($log['durasi_jam']); ?> Jam
                                </td>
                                <td class="py-3.5 px-3 whitespace-nowrap">
                                    <?php
                                    $status = $log['status_persetujuan'];
                                    $badgeStyle = 'bg-slate-100 text-slate-600 border-slate-200';
                                    if ($status === 'Submitted') {
                                        $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-200';
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
                                <td class="py-3.5 px-3 text-slate-500 italic max-w-xs">
                                    <?= !empty($log['catatan_dosen']) ? htmlspecialchars($log['catatan_dosen']) : '<span class="text-slate-300">-</span>'; ?>
                                </td>
                                <td class="py-3.5 px-3 text-right whitespace-nowrap space-x-1">
                                    <?php if ($log['status_persetujuan'] === 'Draft'): ?>
                                        <form action="process_logbook.php" method="POST" class="inline-block">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                                            <input type="hidden" name="action" value="submit">
                                            <input type="hidden" name="logbook_id" value="<?= $log['id']; ?>">
                                            <button type="submit" class="px-2.5 py-1 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-[10px] font-semibold transition-colors">
                                                Submit
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if (in_array($log['status_persetujuan'], ['Draft', 'Revision Required'])): ?>
                                        <button type="button" data-logbook='<?= htmlspecialchars(json_encode($log), ENT_QUOTES, 'UTF-8'); ?>' onclick="openEditModal(this)" class="p-1.5 text-slate-500 hover:text-brand-600 text-xs transition-colors" title="Edit Logbook">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <form action="process_logbook.php" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus logbook ini?')">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="logbook_id" value="<?= $log['id']; ?>">
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 text-xs transition-colors" title="Hapus Logbook">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php elseif (in_array($log['status_persetujuan'], ['Submitted', 'Approved'])): ?>
                                        <span class="text-[10px] text-slate-400 font-semibold px-2 py-1"><i class="fa-solid fa-lock text-[8px] mr-1"></i>Terkunci</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Belum ada catatan logbook harian. Silakan tambah catatan baru!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Input / Edit Logbook -->
<div id="modal-logbook" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-surface-border space-y-4">
        <h3 id="modal-title" class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Input Logbook Harian</h3>
        <form action="process_logbook.php" method="POST" class="space-y-3 text-xs">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
            <input type="hidden" name="action" id="form-action" value="create">
            <input type="hidden" name="logbook_id" id="form-logbook-id" value="">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tanggal Kegiatan *</label>
                <input type="date" name="tanggal" id="form-tanggal" min="<?= $min_date; ?>" max="<?= $max_date; ?>" value="<?= date('Y-m-d') < $max_date ? date('Y-m-d') : $max_date; ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Uraian Kegiatan *</label>
                <textarea name="kegiatan" id="form-kegiatan" rows="4" required placeholder="Jelaskan detail aktivitas magang Anda hari ini..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500"></textarea>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Durasi (Jam) *</label>
                <input type="number" name="durasi_jam" id="form-durasi" min="1" max="24" value="8" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 font-medium transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl font-semibold transition-colors">Simpan Draft</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('modal-title').innerText = 'Input Logbook Harian Baru';
        document.getElementById('form-action').value = 'create';
        document.getElementById('form-logbook-id').value = '';
        document.getElementById('form-tanggal').value = '<?= date('Y-m-d') < $max_date ? date('Y-m-d') : $max_date; ?>';
        document.getElementById('form-kegiatan').value = '';
        document.getElementById('form-durasi').value = '8';
        document.getElementById('modal-logbook').classList.remove('hidden');
    }

    function openEditModal(button) {
        const data = JSON.parse(button.getAttribute('data-logbook'));
        document.getElementById('modal-title').innerText = 'Edit Logbook Harian';
        document.getElementById('form-action').value = 'update';
        document.getElementById('form-logbook-id').value = data.id;
        document.getElementById('form-tanggal').value = data.tanggal;
        document.getElementById('form-kegiatan').value = data.kegiatan;
        document.getElementById('form-durasi').value = data.durasi_jam;
        document.getElementById('modal-logbook').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('modal-logbook').classList.add('hidden');
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>