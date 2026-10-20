<?php
/**
 * MAGANGTRACK - Explore Perusahaan Magang
 * Integration: Gemini Canvas UI (Tailwind CSS) + Dynamic MySQL PDO
 */
require_once __DIR__ . '/auth/auth_middleware.php';
require_once __DIR__ . '/config/database.php';

// Guard Hak Akses Khusus Mahasiswa
requireRole('mahasiswa');

$db = getDBConnection();
$user_id = $_SESSION['user_id'];

// CSRF Protection Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 1. Fetch Profile Data Mahasiswa
$stmtUser = $db->prepare("SELECT id, nama, email, nim_nidn, program_studi FROM users WHERE id = :id");
$stmtUser->execute([':id' => $user_id]);
$student = $stmtUser->fetch();

// 2. Fetch List Sektor Industri Unik (Hanya yang relevan dengan akses user)
$stmtSektor = $db->prepare("
    SELECT DISTINCT sektor_industri 
    FROM companies 
    WHERE (is_verified = 1 OR created_by_user_id = :user_id) 
      AND sektor_industri IS NOT NULL AND sektor_industri != '' 
    ORDER BY sektor_industri ASC
");
$stmtSektor->execute([':user_id' => $user_id]);
$sektorList = $stmtSektor->fetchAll(PDO::FETCH_COLUMN);

// 3. Fetch List Lokasi Unik (Hanya yang relevan dengan akses user)
$stmtLokasi = $db->prepare("
    SELECT DISTINCT lokasi 
    FROM companies 
    WHERE (is_verified = 1 OR created_by_user_id = :user_id) 
      AND lokasi IS NOT NULL AND lokasi != '' 
    ORDER BY lokasi ASC
");
$stmtLokasi->execute([':user_id' => $user_id]);
$lokasiList = $stmtLokasi->fetchAll(PDO::FETCH_COLUMN);

// 4. Fetch Perusahaan: Katalog Publik (is_verified = 1) ATAU Mandiri Milik User Ini
$stmtCompanies = $db->prepare("
    SELECT c.*, u.nama AS pembuat_nama 
    FROM companies c
    LEFT JOIN users u ON c.created_by_user_id = u.id
    WHERE c.is_verified = 1 OR c.created_by_user_id = :user_id
    ORDER BY c.is_verified DESC, c.nama_perusahaan ASC
");
$stmtCompanies->execute([':user_id' => $user_id]);
$companies = $stmtCompanies->fetchAll();

// 5. Cek Status Magang Aktif
$stmtIntern = $db->prepare("SELECT status_magang FROM internships WHERE user_id = :user_id AND status_magang = 'Aktif' LIMIT 1");
$stmtIntern->execute([':user_id' => $user_id]);
$isInternshipActive = ($stmtIntern->fetch() !== false);

// Flash Messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Perusahaan - MAGANGTRACK</title>
    
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
                                <a href="dashboard.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-grid-2 text-sm w-5"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li>
                                <a href="explore.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600 transition-all">
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
                        <input type="text" id="topbar-search" onkeyup="filterCompanyCards()" placeholder="Cari nama perusahaan, industri, atau lokasi..." class="w-full pl-9 pr-12 py-2 text-xs bg-slate-100/80 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
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
                
                <!-- Flash Messages -->
                <?php if ($flashSuccess): ?>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span><?= htmlspecialchars($flashSuccess); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($flashError): ?>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-sm"></i>
                        <span><?= htmlspecialchars($flashError); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Page Header & Action -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Explore Perusahaan Magang</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Temukan atau tambahkan perusahaan tempat magang pilihan kamu.</p>
                    </div>
                    <button onclick="openModal('modal-add-company')" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-xs flex items-center gap-2 transition-colors">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Perusahaan Mandiri</span>
                    </button>
                </div>

                <!-- Search & Multi-Filter Bar -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-white p-3 rounded-2xl border border-surface-border">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cari Perusahaan</label>
                        <div class="relative">
                            <i class="fa-solid fa-building absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" id="filter-keyword" onkeyup="filterCompanyCards()" placeholder="Nama perusahaan..." class="w-full pl-8 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Sektor Industri</label>
                        <select id="filter-industry" onchange="filterCompanyCards()" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-600 focus:outline-none focus:border-brand-500">
                            <option value="">Semua Sektor Industri</option>
                            <?php foreach ($sektorList as $sektor): ?>
                                <option value="<?= htmlspecialchars($sektor); ?>"><?= htmlspecialchars($sektor); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Lokasi Kantor</label>
                        <select id="filter-location" onchange="filterCompanyCards()" class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-600 focus:outline-none focus:border-brand-500">
                            <option value="">Semua Lokasi</option>
                            <?php foreach ($lokasiList as $lokasi): ?>
                                <option value="<?= htmlspecialchars($lokasi); ?>"><?= htmlspecialchars($lokasi); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Company Cards Grid -->
                <div id="companies-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php if (count($companies) > 0): ?>
                        <?php foreach ($companies as $comp): ?>
                            <?php 
                                $words = explode(' ', str_replace(['PT ', '(Persero)', 'Bank '], '', $comp['nama_perusahaan']));
                                $shortCode = '';
                                foreach ($words as $w) {
                                    if (!empty($w)) $shortCode .= strtoupper($w[0]);
                                }
                                $shortCode = substr($shortCode, 0, 4) ?: 'CORP';
                            ?>
                            <div class="company-card p-5 bg-white rounded-2xl border border-surface-border hover:border-brand-300 transition-all flex flex-col justify-between space-y-4"
                                 data-name="<?= htmlspecialchars(strtolower($comp['nama_perusahaan'])); ?>"
                                 data-industry="<?= htmlspecialchars($comp['sektor_industri']); ?>"
                                 data-location="<?= htmlspecialchars($comp['lokasi']); ?>">
                                
                                <div class="space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-bold text-xs flex items-center justify-center tracking-tight shadow-sm">
                                            <?= $shortCode; ?>
                                        </div>
                                        <?php if ($comp['is_verified']): ?>
                                            <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold text-[10px]">Perusahaan Katalog (Simulasi)</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded bg-amber-50 text-amber-700 font-semibold text-[10px]">
                                                Mandiri Saya
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <h4 class="font-bold text-sm text-slate-800"><?= htmlspecialchars($comp['nama_perusahaan']); ?></h4>
                                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                            <i class="fa-solid fa-location-dot text-[10px] text-slate-400"></i>
                                            <?= htmlspecialchars($comp['lokasi']); ?> • <?= htmlspecialchars($comp['sektor_industri']); ?>
                                        </p>
                                    </div>

                                    <p class="text-xs text-slate-600 line-clamp-2">
                                        Perusahaan terdaftar di portal magang. Buka peluang pengembangan karir dan pengalaman kerja profesional.
                                    </p>

                                    <div class="pt-1">
                                        <span class="px-2 py-1 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium inline-block">
                                            <i class="fa-solid fa-envelope text-[9px] text-slate-400 mr-1"></i>
                                            <?= htmlspecialchars($comp['kontak_email'] ?? 'info@perusahaan.com'); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[11px] text-slate-400 font-mono">ID: #COMP-<?= $comp['id']; ?></span>
                                    <button onclick="openApplyModal('<?= $comp['id']; ?>', <?= htmlspecialchars(json_encode($comp['nama_perusahaan']), ENT_QUOTES, 'UTF-8'); ?>)" 
                                            class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5">
                                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                                        <span>Tambah ke Tracker</span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-3 p-8 bg-white rounded-2xl border border-surface-border text-center text-slate-400 text-xs">
                            Belum ada katalog perusahaan terdaftar.
                        </div>
                    <?php endif; ?>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal 1: Tambah Perusahaan Mandiri -->
    <div id="modal-add-company" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-surface-border space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Tambah Perusahaan Mandiri</h3>
                <button onclick="closeModal('modal-add-company')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="process_company.php" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Perusahaan / Instansi *</label>
                    <input type="text" name="nama_perusahaan" placeholder="PT Contoh Teknologi Indonesia" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Sektor Industri *</label>
                    <input type="text" name="sektor_industri" placeholder="misal: Teknologi & Software" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Lokasi Kantor *</label>
                    <input type="text" name="lokasi" placeholder="misal: Jakarta Selatan / Remote" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Email Kontak HR / Informasi</label>
                    <input type="email" name="kontak_email" placeholder="hr@perusahaan.com" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('modal-add-company')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700">Simpan Perusahaan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Modal Tambah ke Application Tracker -->
    <div id="modal-apply" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-surface-border space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Tambah ke Application Tracker</h3>
                <button onclick="closeModal('modal-apply')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form action="process_application.php" method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="company_id" id="apply-company-id">
                
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Perusahaan Terpilih</label>
                    <input type="text" id="apply-company-name" readonly class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl font-semibold text-slate-700">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Posisi Magang yang Dilamar *</label>
                    <input type="text" name="posisi_magang" placeholder="misal: Frontend Developer Intern" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tanggal Melamar *</label>
                    <input type="date" name="tgl_apply" value="<?= date('Y-m-d'); ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Catatan Tambahan</label>
                    <textarea name="catatan" rows="2" placeholder="opsional: via LinkedIn / Website Karir" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('modal-apply')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700">Simpan ke Tracker</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript Logic -->
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function openApplyModal(companyId, companyName) {
            document.getElementById('apply-company-id').value = companyId;
            document.getElementById('apply-company-name').value = companyName;
            openModal('modal-apply');
        }

        function filterCompanyCards() {
            const topbarSearch = document.getElementById('topbar-search').value.toLowerCase();
            const keywordSearch = document.getElementById('filter-keyword').value.toLowerCase();
            const industryFilter = document.getElementById('filter-industry').value;
            const locationFilter = document.getElementById('filter-location').value;

            const query = topbarSearch || keywordSearch;
            const cards = document.querySelectorAll('.company-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const industry = card.getAttribute('data-industry');
                const location = card.getAttribute('data-location');

                const matchesKeyword = !query || name.includes(query) || industry.toLowerCase().includes(query) || location.toLowerCase().includes(query);
                const matchesIndustry = !industryFilter || industry === industryFilter;
                const matchesLocation = !locationFilter || location === locationFilter;

                if (matchesKeyword && matchesIndustry && matchesLocation) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }
    </script>
</body>
</html>