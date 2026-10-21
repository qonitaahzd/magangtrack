<?php
/**
 * MAGANGTRACK - Application Tracker
 * Lokasi berkas: magangtrack/applications.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$csrf_token = generateCSRFToken();

// ------------------------------------------------------------------
// 1. CEK STATUS MAGANG MAHASISWA (BERJALAN / TERJADWAL)
// ------------------------------------------------------------------
$stmtIntern = $db->prepare("
    SELECT id, status_magang 
    FROM internships 
    WHERE user_id = :user_id AND status_magang IN ('Terjadwal', 'Aktif') 
    LIMIT 1
");
$stmtIntern->execute([':user_id' => $user_id]);
$internshipRecord = $stmtIntern->fetch();

$hasRunningRecord = ($internshipRecord !== false);
$isInternshipActive = ($hasRunningRecord && $internshipRecord['status_magang'] === 'Aktif');

// ------------------------------------------------------------------
// 2. FETCH DAFTAR LAMARAN MILIK MAHASISWA
// ------------------------------------------------------------------
$stmtApps = $db->prepare("
    SELECT a.*, c.sektor_industri, c.lokasi 
    FROM applications a
    LEFT JOIN companies c ON a.company_id = c.id
    WHERE a.user_id = :user_id 
    ORDER BY a.tgl_apply DESC
");
$stmtApps->execute([':user_id' => $user_id]);
$applications = $stmtApps->fetchAll();

// ------------------------------------------------------------------
// 3. FETCH DAFTAR DOSEN PEMBIMBING UNTUK MODAL AKTIVASI
// ------------------------------------------------------------------
$stmtDosen = $db->query("SELECT id, nama, nim_nidn FROM users WHERE role = 'dosen' ORDER BY nama ASC");
$dosenList = $stmtDosen->fetchAll();

// Session Flash Messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Application Tracker</h2>
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

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Kelola Status Lamaran</h2>
                <p class="text-xs text-slate-500 mt-0.5">Pantau dan perbarui perkembangan proses magang kamu.</p>
            </div>
            <a href="explore.php" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Cari Perusahaan Lain</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-surface-border p-5 overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase text-[10px]">
                        <th class="py-3 px-3">Perusahaan</th>
                        <th class="py-3 px-3">Posisi</th>
                        <th class="py-3 px-3">Tgl Apply</th>
                        <th class="py-3 px-3">Status Lamaran</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (count($applications) > 0): ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="py-3.5 px-3 font-semibold text-slate-800">
                                    <?= htmlspecialchars($app['nama_perusahaan_snapshot']); ?>
                                    <span class="block text-[10px] text-slate-400 font-normal"><?= htmlspecialchars($app['lokasi'] ?? 'N/A'); ?></span>
                                </td>
                                <td class="py-3.5 px-3 text-slate-600 font-medium"><?= htmlspecialchars($app['posisi_magang']); ?></td>
                                <td class="py-3.5 px-3 text-slate-500"><?= date('d M Y', strtotime($app['tgl_apply'])); ?></td>
                                <td class="py-3.5 px-3">
                                    <form action="process_application_action.php" method="POST" class="inline-block">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="application_id" value="<?= $app['id']; ?>">
                                        <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:border-brand-500 cursor-pointer">
                                            <option value="Applied" <?= $app['status'] === 'Applied' ? 'selected' : ''; ?>>Applied</option>
                                            <option value="Interview Scheduled" <?= $app['status'] === 'Interview Scheduled' ? 'selected' : ''; ?>>Interview Scheduled</option>
                                            <option value="Accepted" <?= $app['status'] === 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                                            <option value="Rejected" <?= $app['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3.5 px-3 text-right space-x-2">
                                    <?php if ($app['status'] === 'Accepted'): ?>
                                        <?php if (!$hasRunningRecord): ?>
                                            <button type="button" onclick="openActivateModal('<?= $app['id']; ?>', '<?= htmlspecialchars(addslashes($app['nama_perusahaan_snapshot'])); ?>')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[11px] font-semibold transition-colors">
                                                Aktivasi Magang
                                            </button>
                                        <?php else: ?>
                                            <span class="text-[10px] bg-slate-100 text-slate-500 px-2.5 py-1 rounded-md font-medium">Magang Aktif</span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <form action="process_application_action.php" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lamaran ini?')">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="application_id" value="<?= $app['id']; ?>">
                                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 text-xs transition-colors" title="Hapus Lamaran">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada lamaran terdaftar. Silakan cari perusahaan di Explore.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Aktivasi Magang Resmi -->
<div id="modal-activate" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-surface-border space-y-4">
        <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2">Form Aktivasi Magang Resmi</h3>
        <form action="process_application_action.php" method="POST" class="space-y-3 text-xs">
            <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
            <input type="hidden" name="action" value="activate_internship">
            <input type="hidden" name="application_id" id="activate-app-id">

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Perusahaan</label>
                <input type="text" id="activate-app-name" readonly class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl font-semibold text-slate-700">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Dosen Pembimbing *</label>
                <select name="dosen_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    <option value="">-- Pilih Dosen Pembimbing --</option>
                    <?php foreach ($dosenList as $d): ?>
                        <option value="<?= $d['id']; ?>"><?= htmlspecialchars($d['nama']); ?> (NIDN: <?= htmlspecialchars($d['nim_nidn']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tanggal Mulai *</label>
                <input type="date" name="tgl_mulai" value="<?= date('Y-m-d'); ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Tanggal Selesai *</label>
                <input type="date" name="tgl_selesai" value="<?= date('Y-m-d', strtotime('+3 months')); ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
            </div>

            <div class="pt-3 flex justify-end gap-2">
                <button type="button" onclick="closeActivateModal()" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 font-medium transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition-colors">Aktivasi Magang</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openActivateModal(id, name) {
        document.getElementById('activate-app-id').value = id;
        document.getElementById('activate-app-name').value = name;
        document.getElementById('modal-activate').classList.remove('hidden');
    }
    function closeActivateModal() {
        document.getElementById('modal-activate').classList.add('hidden');
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>