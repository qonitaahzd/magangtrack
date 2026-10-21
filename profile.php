<?php
/**
 * MAGANGTRACK - Profil Pengguna
 * Lokasi berkas: magangtrack/profile.php
 */

require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

// Pastikan pengguna sudah login (Mahasiswa atau Dosen)
requireLogin();

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// Fetch data profil pengguna langsung dari database
$stmt = $db->prepare("SELECT id, nama, email, nim_nidn, program_studi, role, created_at FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
    <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
        <h2 class="font-bold text-sm text-slate-800">Profil Saya</h2>
        <span class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($user['role'] === 'dosen' ? 'NIDN: ' . $user['nim_nidn'] : 'NIM: ' . $user['nim_nidn']); ?></span>
    </header>

    <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Informasi Akun</h2>
            <p class="text-xs text-slate-500 mt-0.5">Detail identitas dan hak akses Anda pada sistem MAGANGTRACK.</p>
        </div>

        <div class="bg-white rounded-2xl border border-surface-border p-6 shadow-xs max-w-3xl space-y-6">
            <!-- Header Kartu Profil -->
            <div class="flex items-center gap-4 pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-2xl bg-brand-50 border border-brand-200 flex items-center justify-center text-brand-600 text-2xl font-bold">
                    <?= strtoupper(substr($user['nama'], 0, 1)); ?>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($user['nama']); ?></h3>
                    <p class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($user['email']); ?></p>
                    <span class="inline-block mt-2 px-2.5 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase <?= $user['role'] === 'dosen' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-brand-50 text-brand-700 border border-brand-200'; ?>">
                        Role: <?= htmlspecialchars($user['role']); ?>
                    </span>
                </div>
            </div>

            <!-- Detail Data Pengguna -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nama Lengkap</span>
                    <span class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($user['nama']); ?></span>
                </div>

                <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block"><?= $user['role'] === 'dosen' ? 'NIDN' : 'NIM'; ?></span>
                    <span class="font-mono font-bold text-slate-800 text-sm"><?= htmlspecialchars($user['nim_nidn']); ?></span>
                </div>

                <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Alamat Email</span>
                    <span class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($user['email']); ?></span>
                </div>

                <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Program Studi</span>
                    <span class="font-semibold text-slate-800 text-sm"><?= htmlspecialchars($user['program_studi'] ?? 'Belum Diatur'); ?></span>
                </div>

                <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-100 space-y-1 md:col-span-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Terdaftar Sejak</span>
                    <span class="font-medium text-slate-700"><?= date('d F Y, H:i', strtotime($user['created_at'])); ?> WIB</span>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>