<?php
/**
 * MAGANGTRACK - Document Checklist
 * Lokasi berkas: magangtrack/checklist.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];
$csrf_token = generateCSRFToken();

// ------------------------------------------------------------------
// DAFTAR ITEM DOKUMEN STANDAR BILA BLM TERDAFTAR DI DATABASE
// ------------------------------------------------------------------
$defaultItems = [
    'Surat Pengantar Magang (Fakultas/Prodi)',
    'Transkrip Nilai Akademik Terbaru',
    'Curriculum Vitae (CV) ATS-Friendly',
    'Proposal Pengajuan Magang',
    'Surat Penerimaan Magang (Acceptance Letter)',
    'Surat Pernyataan Kesediaan Pembimbing'
];

// Inisialisasi daftar dokumen dasar jika belum pernah ada catatan di database
$stmtCheck = $db->prepare("SELECT COUNT(*) FROM document_checklists WHERE user_id = :user_id");
$stmtCheck->execute([':user_id' => $user_id]);

if ($stmtCheck->fetchColumn() == 0) {
    $stmtInit = $db->prepare("INSERT INTO document_checklists (user_id, nama_dokumen, status) VALUES (:user_id, :nama, 'Belum Lengkap')");
    foreach ($defaultItems as $item) {
        $stmtInit->execute([':user_id' => $user_id, ':nama' => $item]);
    }
}

// Fetch daftar checklist dokumen milik mahasiswa
$stmtDocs = $db->prepare("SELECT * FROM document_checklists WHERE user_id = :user_id ORDER BY id ASC");
$stmtDocs->execute([':user_id' => $user_id]);
$checklists = $stmtDocs->fetchAll();

// Hitung Progres Kelengkapan
$totalDocs = count($checklists);
$completedDocs = 0;
foreach ($checklists as $c) {
    if ($c['status'] === 'Lengkap') $completedDocs++;
}
$completionPercent = $totalDocs > 0 ? round(($completedDocs / $totalDocs) * 100) : 0;

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Checklist Dokumen Magang</h2>
        <span class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($_SESSION['program_studi'] ?? ''); ?></span>
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
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Kelengkapan Persyaratan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Tandai kelengkapan dokumen administratif untuk keperluan pengajuan dan evaluasi magang.</p>
            </div>
            
            <div class="bg-white border border-surface-border px-4 py-2 rounded-xl text-xs min-w-[200px]">
                <div class="flex justify-between font-semibold mb-1">
                    <span class="text-slate-500">Progres Dokumen</span>
                    <span class="text-brand-600 font-bold"><?= $completionPercent; ?>%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-600 rounded-full transition-all duration-300" style="width: <?= $completionPercent; ?>%;"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-surface-border p-5 shadow-xs max-w-3xl">
            <div class="divide-y divide-slate-100">
                <?php foreach ($checklists as $doc): ?>
                    <form action="process_checklist.php" method="POST" class="py-3.5 flex items-center justify-between gap-4">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token; ?>">
                        <input type="hidden" name="doc_id" value="<?= $doc['id']; ?>">
                        
                        <div class="flex items-center gap-3">
                            <button type="submit" name="action" value="toggle" class="w-5 h-5 rounded-md flex items-center justify-center transition-colors <?= $doc['status'] === 'Lengkap' ? 'bg-emerald-600 text-white' : 'border border-slate-300 hover:border-brand-500 text-transparent'; ?>">
                                <i class="fa-solid fa-check text-[10px]"></i>
                            </button>
                            <span class="text-xs font-semibold <?= $doc['status'] === 'Lengkap' ? 'text-slate-800 line-through text-slate-400' : 'text-slate-800'; ?>">
                                <?= htmlspecialchars($doc['nama_dokumen']); ?>
                            </span>
                        </div>

                        <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider <?= $doc['status'] === 'Lengkap' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'; ?>">
                            <?= htmlspecialchars($doc['status']); ?>
                        </span>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>