<?php
/**
 * MAGANGTRACK - Shared Sidebar Component (Layout & Alignment Fixed)
 * Lokasi berkas: magangtrack/includes/sidebar.php
 */

$currentPage     = basename($_SERVER['PHP_SELF']);
$isInDosenFolder = (strpos($_SERVER['PHP_SELF'], '/dosen/') !== false);
$isInAuthFolder  = (strpos($_SERVER['PHP_SELF'], '/auth/') !== false);
$basePath        = ($isInDosenFolder || $isInAuthFolder) ? '../' : '';

$role = $_SESSION['role'] ?? 'mahasiswa';
?>

<!-- Sidebar Container -->
<aside id="main-sidebar" aria-label="Navigasi Utama" class="w-64 bg-white border-r border-surface-border flex flex-col justify-between p-4 flex-shrink-0 absolute md:relative inset-y-0 left-0 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-in-out">
    <div class="space-y-5">
        <!-- Logo & Header Brand -->
        <div class="flex items-center justify-between px-3 py-1">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-brand-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-brand-500/20">
                    <i class="fa-solid fa-graduation-cap text-sm"></i>
                </div>
                <div>
                    <h1 class="font-bold text-base tracking-tight text-slate-900 leading-none">MAGANG<span class="text-brand-600">TRACK</span></h1>
                    <p class="text-[10px] text-slate-400 font-medium tracking-wide uppercase mt-1">
                        <?= $role === 'dosen' ? 'Portal Dosen' : 'Portal Mahasiswa'; ?>
                    </p>
                </div>
            </div>
            <!-- Toggle Close Button Mobile -->
            <button type="button" onclick="toggleSidebar()" aria-label="Tutup Menu Sidebar" class="md:hidden text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-5 overflow-y-auto max-h-[calc(100vh-220px)] pr-1">
            <?php if ($role === 'mahasiswa'): ?>
                <!-- NAVIGASI MAHASISWA: BEFORE INTERNSHIP -->
                <div>
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Before Internship</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= $basePath; ?>dashboard.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= ($currentPage === 'dashboard.php' && !$isInDosenFolder) ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-table-columns text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>explore.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'explore.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-compass text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Explore Perusahaan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>applications.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'applications.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-paper-plane text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Application Tracker</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>checklist.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'checklist.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-list-check text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Document Checklist</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- NAVIGASI MAHASISWA: DURING INTERNSHIP -->
                <div>
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">During Internship</p>
                    <ul class="space-y-1">
                        <!-- My Internship: Tersedia untuk Terjadwal atau Aktif -->
                        <?php $canAccessMyInternship = (bool)($isInternshipScheduled ?? false); ?>
                        <li>
                            <a href="<?= $canAccessMyInternship ? $basePath . 'my-internship.php' : 'javascript:void(0);' ?>" 
                               <?= !$canAccessMyInternship ? 'aria-disabled="true" tabindex="-1"' : '' ?>
                               class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'my-internship.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all <?= !$canAccessMyInternship ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                <div class="flex items-center gap-3 min-w-0">
                                    <i class="fa-solid fa-briefcase text-sm w-5 text-center flex-shrink-0"></i>
                                    <span class="truncate">My Internship</span>
                                </div>
                                <?php if (!$canAccessMyInternship): ?>
                                    <span class="text-[9px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded font-mono ml-2 flex-shrink-0" title="Fitur terkunci hingga magang dijadwalkan/aktif"><i class="fa-solid fa-lock text-[8px]"></i></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <!-- Digital Logbook: Hanya Khusus Magang Berstatus Aktif -->
                        <?php $canAccessLogbook = (bool)($isInternshipActive ?? false); ?>
                        <li>
                            <a href="<?= $canAccessLogbook ? $basePath . 'logbook.php' : 'javascript:void(0);' ?>" 
                               <?= !$canAccessLogbook ? 'aria-disabled="true" tabindex="-1"' : '' ?>
                               class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs <?= ($currentPage === 'logbook.php' && !$isInDosenFolder) ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all <?= !$canAccessLogbook ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                <div class="flex items-center gap-3 min-w-0">
                                    <i class="fa-solid fa-book-bookmark text-sm w-5 text-center flex-shrink-0"></i>
                                    <span class="truncate">Digital Logbook</span>
                                </div>
                                <?php if (!$canAccessLogbook): ?>
                                    <span class="text-[9px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded font-mono ml-2 flex-shrink-0" title="Fitur terkunci hingga status magang aktif"><i class="fa-solid fa-lock text-[8px]"></i></span>
                                <?php endif; ?>
                            </a>
                        </li>

                        <li>
                            <a href="<?= $basePath; ?>calendar.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'calendar.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-calendar-days text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Calendar & Schedule</span>
                            </a>
                        </li>
                    </ul>
                </div>

            <?php else: ?>
                <!-- NAVIGASI DOSEN PEMBIMBING -->
                <div>
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Bimbingan Magang</p>
                    <ul class="space-y-1">
                        <?php 
                        $isDosenDashboardActive = ($currentPage === 'dashboard.php' && $isInDosenFolder);
                        $isDosenLogbookActive   = ($isInDosenFolder && $currentPage === 'logbook.php') || $currentPage === 'dosen_logbook.php';
                        $dosenDashboardLink     = $basePath . 'dosen/dashboard.php';
                        $dosenLogbookLink       = $isInDosenFolder ? 'logbook.php' : $basePath . 'dosen/logbook.php';
                        ?>
                        <li>
                            <a href="<?= $dosenDashboardLink; ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $isDosenDashboardActive ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-users text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Mahasiswa Bimbingan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $dosenLogbookLink; ?>" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $isDosenLogbookActive ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-book-check text-sm w-5 text-center flex-shrink-0"></i>
                                <span class="truncate">Review Logbook</span>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- MENU AKUN (BERLAKU UNTUK SEMUA ROLE) -->
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Account</p>
                <ul class="space-y-1">
                    <li>
                        <a href="<?= $basePath; ?>profile.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'profile.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                            <i class="fa-solid fa-user text-sm w-5 text-center flex-shrink-0"></i>
                            <span class="truncate">My Profile</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- User Profile Widget & Logout Button -->
    <div class="pt-3 border-t border-surface-border">
        <div class="flex items-center gap-3 px-2.5 py-2 rounded-xl bg-slate-50 border border-slate-100">
            <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs flex-shrink-0">
                <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($_SESSION['nama'] ?? ''); ?></p>
                <p class="text-[10px] text-slate-500 truncate font-mono"><?= htmlspecialchars($_SESSION['nim_nidn'] ?? ''); ?></p>
            </div>
            <a href="<?= $basePath; ?>logout.php" aria-label="Logout dari Sistem" class="text-slate-400 hover:text-rose-600 p-1.5 transition-colors flex-shrink-0" title="Logout dari Sistem">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>
</aside>