<?php
/**
 * MAGANGTRACK - Calendar & Deadlines
 */
require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

requireRole('mahasiswa');
$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// Fetch Deadlines
$stmtDeadlines = $db->prepare("SELECT * FROM deadlines WHERE user_id = :user_id ORDER BY tanggal_deadline ASC");
$stmtDeadlines->execute([':user_id' => $user_id]);
$deadlines = $stmtDeadlines->fetchAll();

// Cek status magang
$stmtIntern = $db->prepare("SELECT status_magang FROM internships WHERE user_id = :user_id AND status_magang = 'Aktif' LIMIT 1");
$stmtIntern->execute([':user_id' => $user_id]);
$isInternshipActive = ($stmtIntern->fetch() !== false);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar & Schedule - MAGANGTRACK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: { brand: { 50: '#eef2ff', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }, surface: { bg: '#f8fafc', border: '#e2e8f0' } }
                }
            }
        }
    </script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #0f172a; }</style>
</head>
<body class="h-screen overflow-hidden p-2 sm:p-3 md:p-5">
    <div class="flex h-full w-full bg-surface-bg rounded-2xl shadow-sm border border-surface-border overflow-hidden">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
            <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
                <h2 class="font-bold text-sm text-slate-800">Calendar & Schedule</h2>
                <span class="text-xs text-slate-500"><?= htmlspecialchars($_SESSION['program_studi']); ?></span>
            </header>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Agenda & Tenggat Waktu</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Jadwal kegiatan akademik dan tenggat berkas magang Anda.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (count($deadlines) > 0): ?>
                        <?php foreach ($deadlines as $dl): ?>
                            <div class="p-4 bg-white rounded-2xl border border-surface-border space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-brand-50 text-brand-600"><?= htmlspecialchars($dl['kategori']); ?></span>
                                    <span class="text-[10px] font-mono text-slate-400"><?= date('d M Y', strtotime($dl['tanggal_deadline'])); ?></span>
                                </div>
                                <h4 class="font-bold text-xs text-slate-800"><?= htmlspecialchars($dl['judul_agenda']); ?></h4>
                                <p class="text-xs text-slate-500"><?= htmlspecialchars($dl['keterangan'] ?? '-'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400 col-span-3 text-center py-8">Belum ada agenda terdaftar.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>