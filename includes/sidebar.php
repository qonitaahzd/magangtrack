<?php
/**
 * MAGANGTRACK - Shared Sidebar Navigation
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? 'mahasiswa';

// Deteksi lokasi folder untuk menentukan prefix path
$isInDosenFolder = (strpos($_SERVER['PHP_SELF'], '/dosen/') !== false);
$basePath = $isInDosenFolder ? '../' : '';
?>
<aside class="w-64 bg-white border-r border-surface-border flex flex-col justify-between p-4 hidden md:flex flex-shrink-0">
    <div class="space-y-5">
        <div class="flex items-center gap-3 px-3 py-1">
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

        <nav class="space-y-5 overflow-y-auto max-h-[calc(100vh-220px)] pr-1">
            <?php if ($role === 'mahasiswa'): ?>
                <div>
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Before Internship</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= $basePath; ?>dashboard.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'dashboard.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-grid-2 text-sm w-5"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>explore.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'explore.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-compass text-sm w-5"></i>
                                <span>Explore Perusahaan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>applications.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'applications.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-paper-plane text-sm w-5"></i>
                                <span>Application Tracker</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>checklist.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'checklist.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-list-check text-sm w-5"></i>
                                <span>Document Checklist</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">During Internship</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= $isInternshipActive ? $basePath . 'my-internship.php' : '#' ?>" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'my-internship.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all <?= !$isInternshipActive ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
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
                            <a href="<?= $isInternshipActive ? $basePath . 'logbook.php' : '#' ?>" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'logbook.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all <?= !$isInternshipActive ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' ?>">
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
                            <a href="<?= $basePath; ?>calendar.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'calendar.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-calendar-days text-sm w-5"></i>
                                <span>Calendar & Schedule</span>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div>
                    <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Bimbingan Magang</p>
                    <ul class="space-y-1">
                        <li>
                            <a href="<?= $basePath; ?>dosen/dashboard.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= ($currentPage === 'dashboard.php' && $isInDosenFolder) ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-users text-sm w-5"></i>
                                <span>Mahasiswa Bimbingan</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $basePath; ?>dosen/logbook.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= ($currentPage === 'logbook.php' && $isInDosenFolder) ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                                <i class="fa-solid fa-book-check text-sm w-5"></i>
                                <span>Review Logbook</span>
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>

            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Account</p>
                <ul class="space-y-1">
                    <li>
                        <a href="<?= $basePath; ?>profile.php" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs <?= $currentPage === 'profile.php' ? 'text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600' : 'text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium' ?> transition-all">
                            <i class="fa-solid fa-user text-sm w-5"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div class="pt-3 border-t border-surface-border">
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl bg-slate-50 border border-slate-100">
            <div class="w-8 h-8 rounded-full bg-brand-600 text-white font-bold flex items-center justify-center text-xs">
                <?= strtoupper(substr($_SESSION['nama'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($_SESSION['nama'] ?? ''); ?></p>
                <p class="text-[10px] text-slate-500 truncate"><?= htmlspecialchars($_SESSION['nim_nidn'] ?? ''); ?></p>
            </div>
            <a href="<?= $basePath; ?>logout.php" class="text-slate-400 hover:text-rose-500 p-1 transition-colors" title="Logout">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </a>
        </div>
    </div>
</aside>