<?php
/**
 * MAGANGTRACK - Dashboard Mahasiswa
 * Master Integration: Preserved Gemini Canvas UI (Tailwind CSS) + Dynamic MySQL PDO
 */
require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

// Guard Hak Akses Khusus Mahasiswa
requireRole('mahasiswa');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// 1. Fetch Data Profil Mahasiswa
$stmtUser = $db->prepare("SELECT id, nama, email, nim_nidn, program_studi, no_hp FROM users WHERE id = :id");
$stmtUser->execute([':id' => $user_id]);
$student = $stmtUser->fetch();

// 2. Fetch Record Magang (Terjadwal / Aktif / Selesai)
$stmtIntern = $db->prepare("
    SELECT i.*, 
           a.nama_perusahaan_snapshot, 
           a.posisi_magang,
           d.nama AS nama_dosen,
           d.email AS email_dosen
    FROM internships i
    JOIN applications a ON i.application_id = a.id
    JOIN users d ON i.dosen_id = d.id
    WHERE i.user_id = :user_id AND i.status_magang IN ('Terjadwal', 'Aktif')
    ORDER BY i.created_at DESC
    LIMIT 1
");
$stmtIntern->execute([':user_id' => $user_id]);
$internshipRecord = $stmtIntern->fetch();

// Logika Tahapan Magang
$hasRunningRecord = ($internshipRecord !== false);
$isInternshipActive = ($hasRunningRecord && $internshipRecord['status_magang'] === 'Aktif');
$isInternshipScheduled = ($hasRunningRecord && $internshipRecord['status_magang'] === 'Terjadwal');

// 3. Analytics & Statistics Queries
// A. Total Lamaran Dikirim
$stmtTotalApp = $db->prepare("SELECT COUNT(*) FROM applications WHERE user_id = :user_id");
$stmtTotalApp->execute([':user_id' => $user_id]);
$totalApplications = (int)$stmtTotalApp->fetchColumn();

// B. Total Jam Logbook (MURNI Approved)
$totalApprovedHours = 0;
$totalApprovedCount = 0;
if ($hasRunningRecord) {
    $stmtHours = $db->prepare("
        SELECT COALESCE(SUM(durasi_jam), 0) AS total_jam, COUNT(*) AS total_count 
        FROM logbooks 
        WHERE internship_id = :internship_id AND status_persetujuan = 'Approved'
    ");
    $stmtHours->execute([':internship_id' => $internshipRecord['id']]);
    $logData = $stmtHours->fetch();
    $totalApprovedHours = (int)$logData['total_jam'];
    $totalApprovedCount = (int)$logData['total_count'];
}

// C. Document Checklist Progress (%)
$stmtDoc = $db->prepare("
    SELECT 
        COUNT(*) AS total_doc,
        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) AS completed_doc
    FROM document_checklists 
    WHERE user_id = :user_id
");
$stmtDoc->execute([':user_id' => $user_id]);
$docData = $stmtDoc->fetch();
$totalDocs = (int)($docData['total_doc'] ?? 0);
$completedDocs = (int)($docData['completed_doc'] ?? 0);
$docProgressPercent = ($totalDocs > 0) ? round(($completedDocs / $totalDocs) * 100) : 0;

// 4. Fetch 3 Riwayat Lamaran Terbaru
$stmtAppsList = $db->prepare("
    SELECT a.*, c.sektor_industri, c.lokasi 
    FROM applications a
    LEFT JOIN companies c ON a.company_id = c.id
    WHERE a.user_id = :user_id 
    ORDER BY a.tgl_apply DESC 
    LIMIT 3
");
$stmtAppsList->execute([':user_id' => $user_id]);
$recentApplications = $stmtAppsList->fetchAll();

// 5. Fetch 4 Agenda / Deadlines Terdekat
$stmtDeadlines = $db->prepare("
    SELECT * FROM deadlines 
    WHERE user_id = :user_id AND tanggal_deadline >= CURDATE() 
    ORDER BY tanggal_deadline ASC 
    LIMIT 4
");
$stmtDeadlines->execute([':user_id' => $user_id]);
$upcomingDeadlines = $stmtDeadlines->fetchAll();

// Helper Render Badge Status Lamaran
function renderAppBadge($status) {
    switch ($status) {
        case 'Accepted':
            return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Accepted</span>';
        case 'Interview Scheduled':
            return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Interview Scheduled</span>';
        case 'Rejected':
            return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Rejected</span>';
        default:
            return '<span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Applied</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAGANGTRACK - Portal Mahasiswa</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts & Font Awesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Tailwind Styling Tokens -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        surface: {
                            bg: '#f8fafc',
                            card: '#ffffff',
                            border: '#e2e8f0',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; color: #0f172a; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="h-screen overflow-hidden p-2 sm:p-3 md:p-5">

    <!-- Main Container Shell (UniConnect Style) -->
    <div class="flex h-full w-full bg-surface-bg rounded-2xl shadow-sm border border-surface-border overflow-hidden">
        
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-white border-r border-surface-border flex flex-col justify-between p-4 hidden md:flex flex-shrink-0">
            <div class="space-y-5">
                <!-- Brand Logo -->
                <div class="flex items-center gap-3 px-3 py-1">
                    <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-brand-500/20">
                        <i class="fa-solid fa-graduation-cap text-sm"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-base tracking-tight text-slate-900 leading-none">MAGANG<span class="text-brand-600">TRACK</span></h1>
                        <p class="text-[10px] text-slate-400 font-medium tracking-wide uppercase mt-1">Portal Mahasiswa</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-5 overflow-y-auto max-h-[calc(100vh-220px)] pr-1">
                    <!-- Before Internship Section -->
                    <div>
                        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Before Internship</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="dashboard.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600 transition-all">
                                    <i class="fa-solid fa-grid-2 text-sm w-5"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="explore.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-compass text-sm w-5"></i>
                                    <span>Explore Perusahaan</span>
                                </a>
                            </li>
                            <li>
                                <a href="applications.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-paper-plane text-sm w-5"></i>
                                    <span>Application Tracker</span>
                                </a>
                            </li>
                            <li>
                                <a href="checklist.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-list-check text-sm w-5"></i>
                                    <span>Document Checklist</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- During Internship Section -->
                    <div>
                        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">During Internship</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="<?= $isInternshipActive ? 'my-internship.php' : '#' ?>" 
                                   class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all <?= !$isInternshipActive ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-briefcase text-sm w-5"></i>
                                        <span>My Internship</span>
                                    </div>
                                    <?php if (!$isInternshipActive): ?>
                                        <span class="text-[9px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded font-mono"><i class="fa-solid fa-lock text-[8px]"></i></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="<?= $isInternshipActive ? 'logbook.php' : '#' ?>" 
                                   class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all <?= !$isInternshipActive ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-book-bookmark text-sm w-5"></i>
                                        <span>Digital Logbook</span>
                                    </div>
                                    <?php if (!$isInternshipActive): ?>
                                        <span class="text-[9px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded font-mono"><i class="fa-solid fa-lock text-[8px]"></i></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a href="calendar.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-calendar-days text-sm w-5"></i>
                                    <span>Calendar & Schedule</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- User Account Section -->
                    <div>
                        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Account</p>
                        <ul class="space-y-1">
                            <li>
                                <a href="profile.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-user text-sm w-5"></i>
                                    <span>My Profile</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <!-- Profile & Logout Bottom Bar -->
            <div class="pt-3 border-t border-surface-border">
                <div class="flex items-center gap-3 px-2 py-2 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
                        <?= strtoupper(substr($student['nama'], 0, 1)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($student['nama']); ?></p>
                        <p class="text-[10px] text-slate-500 truncate">NIM: <?= htmlspecialchars($student['nim_nidn']); ?></p>
                    </div>
                    <a href="logout.php" class="text-slate-400 hover:text-rose-500 p-1 transition-colors" title="Logout">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Content Area Wrapper -->
        <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
            
            <!-- Top Bar Header -->
            <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
                <div class="flex items-center gap-3 flex-1 max-w-md">
                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" placeholder="Cari perusahaan, logbook, atau agenda..." class="w-full pl-9 pr-12 py-2 text-xs bg-slate-100/80 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 hidden sm:inline-block">⌘K</span>
                    </div>
                </div>

                <!-- Header Right Actions -->
                <div class="flex items-center gap-3">
                    <button class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 relative">
                        <i class="fa-regular fa-bell text-xs"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                    </button>
                    <div class="h-6 w-px bg-slate-200"></div>
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-semibold text-slate-800"><?= htmlspecialchars($student['program_studi']); ?></p>
                        <p class="text-[10px] text-slate-500">Mahasiswa Aktif</p>
                    </div>
                </div>
            </header>

            <!-- Main Scrollable Content -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                
                <!-- Welcome Banner & Status Callout -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Selamat Datang Kembali, <?= htmlspecialchars(explode(' ', $student['nama'])[0]); ?>! 👋</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Berikut adalah ringkasan progres dan persiapan magang kamu hari ini.</p>
                    </div>
                    <div>
                        <?php if ($isInternshipActive): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Status: Magang Aktif
                            </span>
                        <?php elseif ($isInternshipScheduled): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Status: Magang Terjadwal
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-50 border border-brand-200 text-brand-700 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-brand-500 animate-pulse"></span>
                                Status: Pre-Internship
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Callout Card Banner -->
                <?php if ($isInternshipActive): ?>
                    <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                                <i class="fa-solid fa-briefcase text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-emerald-900">Magang Berjalan di <?= htmlspecialchars($internshipRecord['nama_perusahaan_snapshot']); ?></h4>
                                <p class="text-[11px] text-emerald-700">Posisi: <?= htmlspecialchars($internshipRecord['posisi_magang']); ?> • Dosen Pembimbing: <?= htmlspecialchars($internshipRecord['nama_dosen']); ?></p>
                            </div>
                        </div>
                        <a href="my-internship.php" class="px-3 py-1.5 bg-emerald-600 text-white rounded-xl text-xs font-semibold hover:bg-emerald-700 transition-colors">Detail Magang →</a>
                    </div>
                <?php elseif ($isInternshipScheduled): ?>
                    <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                                <i class="fa-regular fa-clock text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-amber-900">Magang Terjadwal di <?= htmlspecialchars($internshipRecord['nama_perusahaan_snapshot']); ?></h4>
                                <p class="text-[11px] text-amber-700">Periode dimulai tanggal: <strong><?= date('d M Y', strtotime($internshipRecord['tgl_mulai'])); ?></strong>. Akses logbook dibuka saat periode berjalan.</p>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 bg-amber-200 text-amber-800 rounded-xl text-xs font-semibold">Terjadwal</span>
                    </div>
                <?php endif; ?>

                <!-- Stat Cards Grid (SkillHub Analytics Style) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- Card 1: Total Lamaran -->
                    <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                        <div class="flex items-center justify-between text-slate-400 mb-3">
                            <span class="text-xs font-medium text-slate-500">Total Lamaran</span>
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-slate-900"><?= $totalApplications; ?></span>
                            <span class="text-[11px] font-semibold text-emerald-600"><i class="fa-solid fa-arrow-up text-[9px]"></i> Perusahaan</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">Lamaran terkirim</p>
                    </div>

                    <!-- Card 2: Status Magang -->
                    <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                        <div class="flex items-center justify-between text-slate-400 mb-3">
                            <span class="text-xs font-medium text-slate-500">Status Magang</span>
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-circle-check text-xs"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-xl font-bold text-slate-900">
                                <?= $hasRunningRecord ? htmlspecialchars($internshipRecord['status_magang']) : 'Belum Ada'; ?>
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2 truncate">
                            <?= $hasRunningRecord ? htmlspecialchars($internshipRecord['nama_perusahaan_snapshot']) : 'Tahap Persiapan'; ?>
                        </p>
                    </div>

                    <!-- Card 3: Dokumen Berkas -->
                    <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                        <div class="flex items-center justify-between text-slate-400 mb-3">
                            <span class="text-xs font-medium text-slate-500">Dokumen Berkas</span>
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="fa-solid fa-folder-open text-xs"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-slate-900"><?= $docProgressPercent; ?>%</span>
                            <span class="text-[11px] font-semibold text-amber-600"><?= $completedDocs; ?> dari <?= $totalDocs; ?> Dokumen</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3 overflow-hidden">
                            <div class="bg-amber-500 h-1.5 rounded-full" style="width: <?= $docProgressPercent; ?>%"></div>
                        </div>
                    </div>

                    <!-- Card 4: Logbook Jam Terkumpul -->
                    <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                        <div class="flex items-center justify-between text-slate-400 mb-3">
                            <span class="text-xs font-medium text-slate-500">Total Jam Magang</span>
                            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                                <i class="fa-solid fa-book-open text-xs"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-slate-900"><?= $totalApprovedHours; ?></span>
                            <span class="text-[11px] font-semibold text-slate-400">Jam Approved</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-2">
                            <?= $isInternshipActive ? $totalApprovedCount . ' logbook disetujui' : 'Terhitung jika aktif'; ?>
                        </p>
                    </div>

                </div>

                <!-- Main Content Two Columns Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left Column: Status Pengajuan Lamaran -->
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-surface-border p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-bold text-sm text-slate-900">Status Pengajuan Lamaran</h3>
                                <p class="text-xs text-slate-500">Ringkasan riwayat perusahaan yang baru saja kamu lamar.</p>
                            </div>
                            <a href="applications.php" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat Semua →</a>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase text-[10px]">
                                        <th class="py-2.5 px-3">Perusahaan</th>
                                        <th class="py-2.5 px-3">Posisi</th>
                                        <th class="py-2.5 px-3">Tanggal Apply</th>
                                        <th class="py-2.5 px-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (count($recentApplications) > 0): ?>
                                        <?php foreach ($recentApplications as $app): ?>
                                            <tr>
                                                <td class="py-3 px-3 font-semibold text-slate-800"><?= htmlspecialchars($app['nama_perusahaan_snapshot']); ?></td>
                                                <td class="py-3 px-3 text-slate-600"><?= htmlspecialchars($app['posisi_magang']); ?></td>
                                                <td class="py-3 px-3 text-slate-500"><?= date('d M Y', strtotime($app['tgl_apply'])); ?></td>
                                                <td class="py-3 px-3"><?= renderAppBadge($app['status']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-slate-400">Belum ada lamaran yang diajukan.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Agenda & Deadlines -->
                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl border border-surface-border p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="font-bold text-sm text-slate-900">Agenda & Deadlines</h3>
                                <i class="fa-regular fa-calendar-check text-slate-400 text-xs"></i>
                            </div>

                            <div class="space-y-2.5">
                                <?php if (count($upcomingDeadlines) > 0): ?>
                                    <?php foreach ($upcomingDeadlines as $dl): ?>
                                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex-shrink-0 flex flex-col items-center justify-center font-bold text-[10px] leading-tight">
                                                <span><?= date('d', strtotime($dl['tanggal_deadline'])); ?></span>
                                                <span class="uppercase"><?= date('M', strtotime($dl['tanggal_deadline'])); ?></span>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($dl['judul_agenda']); ?></p>
                                                <p class="text-[10px] text-slate-500 truncate"><?= htmlspecialchars($dl['keterangan'] ?? $dl['kategori']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-xs text-slate-400 text-center py-4">Tidak ada agenda terdekat.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </main>
        </div>
    </div>

</body>
</html>