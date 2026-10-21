<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAGANGTRACK - Sistem Informasi Magang Mahasiswa</title>
    
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
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
        }

        /* Clean Soft Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="h-screen overflow-hidden p-2 sm:p-3 md:p-5">

    <!-- Main Container Shell (UniConnect Style Rounded Frame) -->
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
                                <button onclick="switchTab('dashboard')" id="nav-dashboard" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600 transition-all">
                                    <i class="fa-solid fa-grid-2 text-sm w-5"></i>
                                    <span>Dashboard</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="switchTab('explore')" id="nav-explore" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-compass text-sm w-5"></i>
                                    <span>Explore Perusahaan</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="switchTab('applications')" id="nav-applications" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-paper-plane text-sm w-5"></i>
                                    <span>Application Tracker</span>
                                </button>
                            </li>
                            <li>
                                <button onclick="switchTab('documents')" id="nav-documents" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-list-check text-sm w-5"></i>
                                    <span>Document Checklist</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- During Internship Section -->
                    <div>
                        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">During Internship</p>
                        <ul class="space-y-1">
                            <li>
                                <button onclick="switchTab('my-internship')" id="nav-my-internship" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-briefcase text-sm w-5"></i>
                                        <span>My Internship</span>
                                    </div>
                                    <span id="badge-lock-my-internship" class="text-[9px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded font-mono hidden"><i class="fa-solid fa-lock text-[8px]"></i></span>
                                </button>
                            </li>
                            <li>
                                <button onclick="switchTab('logbook')" id="nav-logbook" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <div class="flex items-center gap-3">
                                        <i class="fa-solid fa-book-bookmark text-sm w-5"></i>
                                        <span>Digital Logbook</span>
                                    </div>
                                    <span id="badge-lock-logbook" class="text-[9px] bg-slate-100 text-slate-400 px-1.5 py-0.5 rounded font-mono hidden"><i class="fa-solid fa-lock text-[8px]"></i></span>
                                </button>
                            </li>
                            <li>
                                <button onclick="switchTab('calendar')" id="nav-calendar" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-calendar-days text-sm w-5"></i>
                                    <span>Calendar & Schedule</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- User Account Section -->
                    <div>
                        <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Account</p>
                        <ul class="space-y-1">
                            <li>
                                <button onclick="switchTab('profile')" id="nav-profile" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all">
                                    <i class="fa-solid fa-user text-sm w-5"></i>
                                    <span>My Profile</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>

            <!-- Profile & Logout Bottom Bar -->
            <div class="pt-3 border-t border-surface-border">
                <div onclick="switchTab('profile')" class="flex items-center gap-3 px-2 py-2 rounded-xl bg-slate-50 border border-slate-100 cursor-pointer hover:bg-slate-100 transition-colors">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80" alt="Avatar" class="w-8 h-8 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate user-display-name">Qonita Husna Zahida</p>
                        <p class="text-[10px] text-slate-500 truncate user-display-nim">NIM: I43252605</p>
                    </div>
                    <button class="text-slate-400 hover:text-rose-500 p-1 transition-colors" title="Logout">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    </button>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col h-full overflow-hidden bg-slate-50/50 min-w-0">
            
            <!-- Top Bar Header -->
            <header class="h-16 px-4 md:px-6 bg-white border-b border-surface-border flex items-center justify-between gap-4 flex-shrink-0">
                <div class="flex items-center gap-3 flex-1 max-w-md">
                    <button onclick="toggleMobileSidebar()" class="md:hidden p-2 text-slate-600 rounded-xl border border-slate-200 hover:bg-slate-50">
                        <i class="fa-solid fa-bars text-sm"></i>
                    </button>

                    <div class="relative w-full">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" placeholder="Cari perusahaan, logbook, atau agenda..." class="w-full pl-9 pr-12 py-2 text-xs bg-slate-100/80 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 focus:bg-white transition-all">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400 font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 hidden sm:inline-block">⌘K</span>
                    </div>
                </div>

                <!-- Demo Stage Switcher Toggle -->
                <div class="hidden lg:flex items-center gap-2 bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-500 px-2 uppercase">Skenario Demo:</span>
                    <button id="btn-scenario-pre" onclick="setScenario('pre')" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white text-brand-600 shadow-xs transition-all">
                        1. Pre-Internship
                    </button>
                    <button id="btn-scenario-during" onclick="setScenario('during')" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg text-slate-600 hover:text-slate-900 transition-all">
                        2. Magang Aktif
                    </button>
                </div>

                <!-- Header Right Actions -->
                <div class="flex items-center gap-3">
                    <button class="w-9 h-9 rounded-xl border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-50 relative">
                        <i class="fa-regular fa-bell text-xs"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-rose-500"></span>
                    </button>
                    <div class="h-6 w-px bg-slate-200"></div>
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-semibold text-slate-800">Teknik Informatika</p>
                        <p class="text-[10px] text-slate-500">Semester 6 - Reguler</p>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">
                
                <!-- ================= 1. TAB DASHBOARD ================= -->
                <div id="tab-dashboard" class="space-y-6">
                    <!-- Welcome Hero Header -->
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Selamat Datang Kembali, Qonita! 👋</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Berikut adalah ringkasan progres dan persiapan magang kamu minggu ini.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="dashboard-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                Status: Pre-Internship
                            </span>
                        </div>
                    </div>

                    <!-- Stat Cards Grid (SkillHub Analytics Style) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                            <div class="flex items-center justify-between text-slate-400 mb-3">
                                <span class="text-xs font-medium text-slate-500">Total Lamaran</span>
                                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-bold text-slate-900">5</span>
                                <span class="text-[11px] font-semibold text-emerald-600"><i class="fa-solid fa-arrow-up text-[9px]"></i> 3 Perusahaan</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-2">1 Menunggu Respon</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                            <div class="flex items-center justify-between text-slate-400 mb-3">
                                <span class="text-xs font-medium text-slate-500">Status Magang</span>
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <i class="fa-solid fa-circle-check text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span id="stat-magang-status" class="text-2xl font-bold text-slate-900">1</span>
                                <span id="stat-magang-label" class="text-[11px] font-semibold text-emerald-600">Diterima</span>
                            </div>
                            <p id="stat-magang-detail" class="text-[11px] text-slate-400 mt-2">PT Telkom Indonesia</p>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                            <div class="flex items-center justify-between text-slate-400 mb-3">
                                <span class="text-xs font-medium text-slate-500">Dokumen Berkas</span>
                                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                    <i class="fa-solid fa-folder-open text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span class="text-2xl font-bold text-slate-900">75%</span>
                                <span class="text-[11px] font-semibold text-amber-600">3 dari 4 Dokumen</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-3 overflow-hidden">
                                <div class="bg-amber-500 h-1.5 rounded-full" style="width: 75%"></div>
                            </div>
                        </div>

                        <div class="p-5 rounded-2xl bg-white border border-surface-border shadow-xs hover:border-brand-200 transition-all">
                            <div class="flex items-center justify-between text-slate-400 mb-3">
                                <span class="text-xs font-medium text-slate-500">Logbook Terisi</span>
                                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                                    <i class="fa-solid fa-book-open text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-2">
                                <span id="stat-logbook-hours" class="text-2xl font-bold text-slate-900">0</span>
                                <span class="text-[11px] font-semibold text-slate-400">Jam Terkumpul</span>
                            </div>
                            <p id="stat-logbook-count" class="text-[11px] text-slate-400 mt-2">Belum Mulai Magang</p>
                        </div>
                    </div>

                    <!-- Main Dashboard Content Area -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left 2 Cols: Recent Applications -->
                        <div class="lg:col-span-2 bg-white rounded-2xl border border-surface-border p-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="font-bold text-sm text-slate-900">Status Pengajuan Lamaran</h3>
                                    <p class="text-xs text-slate-500">Ringkasan perusahaan yang baru saja kamu lamar.</p>
                                </div>
                                <button onclick="switchTab('applications')" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lihat Semua →</button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="text-slate-400 border-b border-slate-100 font-semibold uppercase text-[10px]">
                                            <th class="py-2.5 px-3">Perusahaan</th>
                                            <th class="py-2.5 px-3">Posisi</th>
                                            <th class="py-2.5 px-3">Tanggal Apply</th>
                                            <th class="py-2.5 px-3">Status</th>
                                            <th class="py-2.5 px-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr>
                                            <td class="py-3 px-3 font-semibold text-slate-800">PT Telkom Indonesia</td>
                                            <td class="py-3 px-3 text-slate-600">Frontend Developer Intern</td>
                                            <td class="py-3 px-3 text-slate-500">12 Okt 2026</td>
                                            <td class="py-3 px-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Accepted</span>
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <button onclick="openModal('modal-activate-internship')" class="px-2.5 py-1 text-[11px] font-semibold bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-colors">Aktifkan Magang</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 px-3 font-semibold text-slate-800">GoTo Group (Gojek Tokopedia)</td>
                                            <td class="py-3 px-3 text-slate-600">UI/UX Designer Intern</td>
                                            <td class="py-3 px-3 text-slate-500">10 Okt 2026</td>
                                            <td class="py-3 px-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Interview Scheduled</span>
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <button class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="py-3 px-3 font-semibold text-slate-800">Bank BRI</td>
                                            <td class="py-3 px-3 text-slate-600">IT Data Analyst Intern</td>
                                            <td class="py-3 px-3 text-slate-500">01 Okt 2026</td>
                                            <td class="py-3 px-3">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Rejected</span>
                                            </td>
                                            <td class="py-3 px-3 text-right">
                                                <button class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Right Col: Agenda & Deadlines -->
                        <div class="space-y-4">
                            <div class="bg-white rounded-2xl border border-surface-border p-5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-bold text-sm text-slate-900">Agenda & Deadlines</h3>
                                    <i class="fa-regular fa-calendar-check text-slate-400 text-xs"></i>
                                </div>
                                <div class="space-y-2.5">
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex-shrink-0 flex flex-col items-center justify-center font-bold text-[10px] leading-tight">
                                            <span>24</span>
                                            <span>OKT</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate">Submit Surat Pengantar Kampus</p>
                                            <p class="text-[10px] text-slate-500">Batas akhir kelengkapan berkas</p>
                                        </div>
                                    </div>
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex-shrink-0 flex flex-col items-center justify-center font-bold text-[10px] leading-tight">
                                            <span>28</span>
                                            <span>OKT</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-slate-800 truncate">Interview PT Telkom Indonesia</p>
                                            <p class="text-[10px] text-slate-500">Google Meet - 10:00 WIB</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 2. TAB EXPLORE COMPANIES ================= -->
                <div id="tab-explore" class="space-y-6 hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Explore Perusahaan Magang</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Temukan atau tambahkan perusahaan referensi magang kamu.</p>
                        </div>
                        <button onclick="openModal('modal-add-company')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Tambah Perusahaan Mandiri</span>
                        </button>
                    </div>

                    <!-- Search & Filter Bar -->
                    <div class="flex flex-wrap items-center gap-3 bg-white p-3 rounded-2xl border border-surface-border">
                        <input type="text" id="input-search-company" onkeyup="filterCompanyCards()" placeholder="Cari nama perusahaan atau posisi..." class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500 w-full sm:w-64">
                        <select id="select-filter-industry" onchange="filterCompanyCards()" class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl text-slate-600">
                            <option value="">Semua Sektor Industri</option>
                            <option value="Teknologi & Telekomunikasi">Teknologi & Telekomunikasi</option>
                            <option value="Energi & Sumber Daya">Energi & Sumber Daya</option>
                            <option value="Keuangan & Perbankan">Keuangan & Perbankan</option>
                            <option value="Digital & E-Commerce">Digital & E-Commerce</option>
                        </select>
                    </div>

                    <!-- Company Cards Grid (5 Requested Top Companies) -->
                    <div id="company-cards-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        
                        <!-- 1. PT Telkom Indonesia -->
                        <div class="company-card p-5 bg-white rounded-2xl border border-surface-border hover:border-brand-300 transition-all space-y-4 flex flex-col justify-between" data-name="PT Telkom Indonesia" data-industry="Teknologi & Telekomunikasi">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="w-10 h-10 rounded-xl bg-red-600 text-white font-bold text-xs flex items-center justify-center tracking-tight">
                                        TLKM
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold text-[10px]">Verified BUMN</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">PT Telkom Indonesia</h4>
                                    <p class="text-xs text-slate-500">Jakarta Selatan • Teknologi & Telekomunikasi</p>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2">BUMN telekomunikasi digital terbesar di Indonesia. Membuka posisi magang untuk divisi Software Engineering, Data Science, dan Network Architecture.</p>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">Frontend Developer</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">Cloud Engineer</span>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kontak: hr@telkom.co.id</span>
                                <button onclick="quickApply('PT Telkom Indonesia', 'Frontend Developer Intern')" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold transition-colors">Apply</button>
                            </div>
                        </div>

                        <!-- 2. PT PLN (Persero) -->
                        <div class="company-card p-5 bg-white rounded-2xl border border-surface-border hover:border-brand-300 transition-all space-y-4 flex flex-col justify-between" data-name="PT PLN (Persero)" data-industry="Energi & Sumber Daya">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="w-10 h-10 rounded-xl bg-sky-600 text-white font-bold text-xs flex items-center justify-center tracking-tight">
                                        PLN
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold text-[10px]">Verified BUMN</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">PT PLN (Persero)</h4>
                                    <p class="text-xs text-slate-500">Jakarta Kebayoran Baru • Energi & Sumber Daya</p>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2">Perusahaan Listrik Negara membuka peluang magang divisi Sistem Informasi, IoT Smart Grid, dan Otomasi Industri.</p>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">System Analyst</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">IoT Specialist</span>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kontak: karir@pln.co.id</span>
                                <button onclick="quickApply('PT PLN (Persero)', 'System Analyst Intern')" class="px-3 py-1.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 rounded-xl text-xs font-semibold transition-colors">Apply</button>
                            </div>
                        </div>

                        <!-- 3. PT Pertamina (Persero) -->
                        <div class="company-card p-5 bg-white rounded-2xl border border-surface-border hover:border-brand-300 transition-all space-y-4 flex flex-col justify-between" data-name="PT Pertamina (Persero)" data-industry="Energi & Sumber Daya">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white font-bold text-xs flex items-center justify-center tracking-tight">
                                        PER
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold text-[10px]">Verified BUMN</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">PT Pertamina (Persero)</h4>
                                    <p class="text-xs text-slate-500">Jakarta Pusat • Energi & Sumber Daya</p>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2">BUMN energi nasional. Menawarkan posisi magang di bidang Enterprise IT Security, Mobile App Development, dan Business Analytics.</p>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">Cyber Security</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">Data Engineer</span>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kontak: recruitment@pertamina.com</span>
                                <button onclick="quickApply('PT Pertamina (Persero)', 'Data Engineer Intern')" class="px-3 py-1.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 rounded-xl text-xs font-semibold transition-colors">Apply</button>
                            </div>
                        </div>

                        <!-- 4. Bank BRI -->
                        <div class="company-card p-5 bg-white rounded-2xl border border-surface-border hover:border-brand-300 transition-all space-y-4 flex flex-col justify-between" data-name="Bank BRI" data-industry="Keuangan & Perbankan">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="w-10 h-10 rounded-xl bg-blue-700 text-white font-bold text-xs flex items-center justify-center tracking-tight">
                                        BRI
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 font-semibold text-[10px]">Mitra Kampus</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">Bank Rakyat Indonesia (BRI)</h4>
                                    <p class="text-xs text-slate-500">Jakarta Pusat • Keuangan & Perbankan</p>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2">Bank BUMN perintis digital banking. Membuka program magang untuk pengembangan aplikasi BRImo, AI Fraud Detection, dan QA Engineering.</p>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">Backend Developer</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">QA Engineer</span>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kontak: intern@bri.co.id</span>
                                <button onclick="quickApply('Bank BRI', 'Backend Developer Intern')" class="px-3 py-1.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 rounded-xl text-xs font-semibold transition-colors">Apply</button>
                            </div>
                        </div>

                        <!-- 5. GoTo Group -->
                        <div class="company-card p-5 bg-white rounded-2xl border border-surface-border hover:border-brand-300 transition-all space-y-4 flex flex-col justify-between" data-name="GoTo Group" data-industry="Digital & E-Commerce">
                            <div class="space-y-3">
                                <div class="flex items-start justify-between">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white font-bold text-xs flex items-center justify-center tracking-tight">
                                        GOTO
                                    </div>
                                    <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-600 font-semibold text-[10px]">Verified Tech</span>
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800">GoTo Group (Gojek Tokopedia)</h4>
                                    <p class="text-xs text-slate-500">Jakarta Selatan • Digital & E-Commerce</p>
                                </div>
                                <p class="text-xs text-slate-600 line-clamp-2">Ekosistem digital terdepan di Indonesia. Program magang di bidang UI/UX Product Design, Growth Product Manager, dan Android Engineering.</p>
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">UI/UX Designer</span>
                                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[10px] font-medium">Product Manager</span>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span class="text-[11px] text-slate-400">Kontak: talent@gotocompany.com</span>
                                <button onclick="quickApply('GoTo Group', 'UI/UX Designer Intern')" class="px-3 py-1.5 bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 rounded-xl text-xs font-semibold transition-colors">Apply</button>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ================= 3. TAB APPLICATION TRACKER ================= -->
                <div id="tab-applications" class="space-y-6 hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Application Tracker</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pantau seluruh status lamaran magang yang telah kamu kirimkan.</p>
                        </div>
                        <button onclick="switchTab('explore')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-xs flex items-center gap-2">
                            <i class="fa-solid fa-plus text-xs"></i>
                            <span>Pilih Dari Perusahaan</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="p-4 bg-white rounded-2xl border border-surface-border text-center">
                            <p class="text-xs text-slate-500 font-medium">Applied</p>
                            <p class="text-xl font-bold text-slate-800 mt-1">1</p>
                        </div>
                        <div class="p-4 bg-white rounded-2xl border border-surface-border text-center">
                            <p class="text-xs text-amber-600 font-medium">Interview Scheduled</p>
                            <p class="text-xl font-bold text-amber-600 mt-1">1</p>
                        </div>
                        <div class="p-4 bg-white rounded-2xl border border-surface-border text-center">
                            <p class="text-xs text-emerald-600 font-medium">Accepted</p>
                            <p class="text-xl font-bold text-emerald-600 mt-1">1</p>
                        </div>
                        <div class="p-4 bg-white rounded-2xl border border-surface-border text-center">
                            <p class="text-xs text-rose-600 font-medium">Rejected</p>
                            <p class="text-xl font-bold text-rose-600 mt-1">1</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-surface-border overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] border-b border-surface-border">
                                <tr>
                                    <th class="py-3 px-4">Nama Perusahaan</th>
                                    <th class="py-3 px-4">Posisi</th>
                                    <th class="py-3 px-4">Tanggal Apply</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi / Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="application-table-body" class="divide-y divide-slate-100">
                                <tr>
                                    <td class="py-4 px-4 font-semibold text-slate-800">PT Telkom Indonesia</td>
                                    <td class="py-4 px-4 text-slate-600">Frontend Developer Intern</td>
                                    <td class="py-4 px-4 text-slate-500">12 Okt 2026</td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Accepted</span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <button onclick="openModal('modal-activate-internship')" class="px-3 py-1.5 bg-brand-600 text-white rounded-xl text-xs font-semibold hover:bg-brand-700 transition-colors">Aktifkan Magang</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-4 px-4 font-semibold text-slate-800">GoTo Group (Gojek Tokopedia)</td>
                                    <td class="py-4 px-4 text-slate-600">UI/UX Designer Intern</td>
                                    <td class="py-4 px-4 text-slate-500">10 Okt 2026</td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Interview Scheduled</span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <button class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-pen-to-square"></i> Update</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-4 px-4 font-semibold text-slate-800">PT Pertamina (Persero)</td>
                                    <td class="py-4 px-4 text-slate-600">Data Engineer Intern</td>
                                    <td class="py-4 px-4 text-slate-500">05 Okt 2026</td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Applied</span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <button class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-pen-to-square"></i> Update</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-4 px-4 font-semibold text-slate-800">Bank BRI</td>
                                    <td class="py-4 px-4 text-slate-600">IT Data Analyst Intern</td>
                                    <td class="py-4 px-4 text-slate-500">01 Okt 2026</td>
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Rejected</span>
                                    </td>
                                    <td class="py-4 px-4 text-right">
                                        <span class="text-slate-400 italic text-[11px]">Selesai</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ================= 4. TAB DOCUMENT CHECKLIST ================= -->
                <div id="tab-documents" class="space-y-6 hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Document Checklist</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola dan centang kesiapan berkas pendaftaran magang kamu.</p>
                        </div>
                        <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 font-semibold text-xs border border-amber-200">
                            Status Progres: 3 / 4 Selesai (75%)
                        </span>
                    </div>

                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-brand-600 h-2 rounded-full" style="width: 75%"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-5 bg-white rounded-2xl border border-surface-border flex items-start gap-4">
                            <input type="checkbox" checked class="mt-1 w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                            <div class="flex-1">
                                <h4 class="font-bold text-xs text-slate-800 line-through">CV Terbaru (Curriculum Vitae)</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Sudah diperbarui dengan portofolio semester 5.</p>
                            </div>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Siap</span>
                        </div>

                        <div class="p-5 bg-white rounded-2xl border border-surface-border flex items-start gap-4">
                            <input type="checkbox" checked class="mt-1 w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                            <div class="flex-1">
                                <h4 class="font-bold text-xs text-slate-800 line-through">Transkrip Nilai Akademik</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Transkrip resmi dari Portal Akademik Kampus.</p>
                            </div>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Siap</span>
                        </div>

                        <div class="p-5 bg-white rounded-2xl border border-surface-border flex items-start gap-4">
                            <input type="checkbox" checked class="mt-1 w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                            <div class="flex-1">
                                <h4 class="font-bold text-xs text-slate-800 line-through">Portofolio Design & Code</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Link Figma & GitHub project terlampir di CV.</p>
                            </div>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Siap</span>
                        </div>

                        <div class="p-5 bg-white rounded-2xl border border-surface-border flex items-start gap-4">
                            <input type="checkbox" class="mt-1 w-4 h-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                            <div class="flex-1">
                                <h4 class="font-bold text-xs text-slate-800">Surat Pengantar Magang Kampus</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5">Menunggu stempel dan TTD dari Ketua Program Studi.</p>
                            </div>
                            <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Pending</span>
                        </div>
                    </div>
                </div>

                <!-- ================= 5. TAB MY INTERNSHIP ================= -->
                <div id="tab-my-internship" class="space-y-6 hidden">
                    <!-- Stage Lock Callout Banner -->
                    <div id="banner-my-internship-locked" class="p-5 bg-amber-50 rounded-2xl border border-amber-200 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 font-bold text-lg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-amber-900">Magang Belum Diaktifkan</h4>
                            <p class="text-xs text-amber-700 mt-1">Kamu saat ini berada pada tahap **Pre-Internship**. Untuk membuka fitur ini, pastikan salah satu status pengajuan di Application Tracker telah diterima (Accepted), lalu klik tombol **"Aktifkan Magang"**.</p>
                            <button onclick="switchTab('applications')" class="mt-3 px-3 py-1.5 bg-amber-600 text-white rounded-xl text-xs font-semibold hover:bg-amber-700 transition-colors">Ke Application Tracker →</button>
                        </div>
                    </div>

                    <!-- Active Content Container -->
                    <div id="content-my-internship-active" class="space-y-6 hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 tracking-tight">My Active Internship</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Informasi dan statistik progres magang yang sedang berjalan.</p>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Status: Magang Aktif
                            </span>
                        </div>

                        <!-- Company Hero Card -->
                        <div class="p-6 bg-white rounded-2xl border border-surface-border space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-red-600 text-white font-bold text-sm flex items-center justify-center">
                                        TLKM
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-base text-slate-900">PT Telkom Indonesia</h3>
                                        <p class="text-xs text-slate-500">Posisi: Frontend Developer Intern</p>
                                    </div>
                                </div>
                                <div class="text-left sm:text-right">
                                    <p class="text-xs text-slate-400">Periode Magang</p>
                                    <p class="text-xs font-semibold text-slate-800">01 Nov 2026 - 28 Feb 2027 (4 Bulan)</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                                <div>
                                    <p class="text-[11px] text-slate-400 font-medium">Dosen Pembimbing</p>
                                    <p class="text-xs font-semibold text-slate-800 mt-0.5">Dr. Ir. Ahmad Dahlan, M.T.</p>
                                    <p class="text-[10px] text-slate-500">NIDN: 041208801</p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-400 font-medium">Lokasi Kantor</p>
                                    <p class="text-xs font-semibold text-slate-800 mt-0.5">Jakarta Selatan (Hybrid)</p>
                                </div>
                                <div>
                                    <p class="text-[11px] text-slate-400 font-medium">Total Jam Terkumpul</p>
                                    <p class="text-xs font-semibold text-brand-600 mt-0.5">16 Jam / 600 Jam Target</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 6. TAB DIGITAL LOGBOOK ================= -->
                <div id="tab-logbook" class="space-y-6 hidden">
                    <!-- Stage Lock Callout Banner -->
                    <div id="banner-logbook-locked" class="p-5 bg-amber-50 rounded-2xl border border-amber-200 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 font-bold text-lg">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-amber-900">Logbook Belum Terbuka</h4>
                            <p class="text-xs text-amber-700 mt-1">Pengisian logbook harian hanya dapat diakses setelah kamu **mengaktifkan status magang resmi**. Silakan ubah status lamaran yang diterima di Application Tracker terlebih dahulu.</p>
                        </div>
                    </div>

                    <!-- Active Content Container -->
                    <div id="content-logbook-active" class="space-y-6 hidden">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Digital Logbook Harian</h2>
                                <p class="text-xs text-slate-500 mt-0.5">Catat seluruh kegiatan magang harian kamu untuk disetujui Dosen Pembimbing.</p>
                            </div>
                            <button onclick="openModal('modal-add-logbook')" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-semibold shadow-xs flex items-center gap-2">
                                <i class="fa-solid fa-plus text-xs"></i>
                                <span>Isi Logbook Baru</span>
                            </button>
                        </div>

                        <!-- Logbook Filter Tabs -->
                        <div class="flex items-center gap-2 border-b border-slate-200 pb-2 text-xs font-medium text-slate-500 overflow-x-auto">
                            <button class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-brand-600 font-semibold shadow-xs">Semua Logbook</button>
                            <button class="px-3 py-1.5 hover:text-slate-800">Draft</button>
                            <button class="px-3 py-1.5 hover:text-slate-800">Submitted</button>
                            <button class="px-3 py-1.5 hover:text-slate-800">Approved</button>
                            <button class="px-3 py-1.5 hover:text-slate-800">Revision Required</button>
                        </div>

                        <!-- Logbook Table -->
                        <div class="bg-white rounded-2xl border border-surface-border overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] border-b border-surface-border">
                                    <tr>
                                        <th class="py-3 px-4">Tanggal</th>
                                        <th class="py-3 px-4">Deskripsi Kegiatan</th>
                                        <th class="py-3 px-4">Durasi</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4">Catatan Dosen</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr>
                                        <td class="py-4 px-4 font-semibold text-slate-800">02 Nov 2026</td>
                                        <td class="py-4 px-4 text-slate-600 max-w-xs">Onboarding tim frontend & setup environment repository React Tailwind.</td>
                                        <td class="py-4 px-4 text-slate-500">8 Jam</td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Approved</span>
                                        </td>
                                        <td class="py-4 px-4 text-slate-600 text-[11px]">Bagus, lanjutkan dokumentasi setup-nya.</td>
                                        <td class="py-4 px-4 text-right">
                                            <span class="text-slate-400 text-[11px]">Selesai</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-4 px-4 font-semibold text-slate-800">03 Nov 2026</td>
                                        <td class="py-4 px-4 text-slate-600 max-w-xs">Slicing komponen Dashboard UI & integrasi API data pengguna.</td>
                                        <td class="py-4 px-4 text-slate-500">8 Jam</td>
                                        <td class="py-4 px-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Submitted</span>
                                        </td>
                                        <td class="py-4 px-4 text-slate-400 italic text-[11px]">- Belum direview -</td>
                                        <td class="py-4 px-4 text-right">
                                            <button class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-pen-to-square"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= 7. TAB CALENDAR & SCHEDULE ================= -->
                <div id="tab-calendar" class="space-y-6 hidden">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Calendar & Deadlines</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Jadwal penting, tenggat waktu berkas, dan sesi bimbingan magang.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-50">Okt 2026</button>
                            <button class="px-3 py-1.5 bg-brand-600 text-white rounded-xl text-xs font-semibold hover:bg-brand-700">+ Tambah Agenda</button>
                        </div>
                    </div>

                    <!-- Calendar Grid View -->
                    <div class="bg-white rounded-2xl border border-surface-border p-5 space-y-4">
                        <div class="grid grid-cols-7 gap-2 text-center text-xs font-bold text-slate-400 uppercase border-b border-slate-100 pb-2">
                            <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
                        </div>
                        <div class="grid grid-cols-7 gap-2 text-xs">
                            <div class="h-20 p-1 bg-slate-50 rounded-xl text-slate-300">19</div>
                            <div class="h-20 p-1 bg-slate-50 rounded-xl text-slate-300">20</div>
                            <div class="h-20 p-1 bg-white border border-slate-200 rounded-xl font-semibold text-slate-800">21</div>
                            <div class="h-20 p-1 bg-white border border-slate-200 rounded-xl font-semibold text-slate-800">22</div>
                            <div class="h-20 p-1 bg-white border border-slate-200 rounded-xl font-semibold text-slate-800">23</div>
                            <div class="h-20 p-1 bg-indigo-50 border border-indigo-200 rounded-xl font-semibold text-brand-600 p-1">
                                <span>24</span>
                                <div class="mt-1 p-1 bg-brand-600 text-white text-[9px] rounded font-normal leading-tight truncate">Submit Berkas</div>
                            </div>
                            <div class="h-20 p-1 bg-white border border-slate-200 rounded-xl font-semibold text-slate-800">25</div>
                            <div class="h-20 p-1 bg-white border border-slate-200 rounded-xl font-semibold text-slate-800">26</div>
                            <div class="h-20 p-1 bg-white border border-slate-200 rounded-xl font-semibold text-slate-800">27</div>
                            <div class="h-20 p-1 bg-amber-50 border border-amber-200 rounded-xl font-semibold text-amber-700 p-1">
                                <span>28</span>
                                <div class="mt-1 p-1 bg-amber-500 text-white text-[9px] rounded font-normal leading-tight truncate">Interview</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= 8. TAB MY PROFILE ================= -->
                <div id="tab-profile" class="space-y-6 hidden">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">My Profile</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kelola informasi data diri dan kata sandi akun kamu.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Profile Card Left -->
                        <div class="bg-white p-6 rounded-2xl border border-surface-border text-center space-y-4">
                            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80" alt="Avatar" class="w-24 h-24 rounded-full object-cover mx-auto ring-4 ring-slate-100">
                            <div>
                                <h3 class="font-bold text-base text-slate-900 user-display-name">Qonita Husna Zahida</h3>
                                <p class="text-xs text-slate-500 user-display-nim">NIM: I43252605</p>
                            </div>
                            <div class="pt-3 border-t border-slate-100 text-left text-xs space-y-2 text-slate-600">
                                <p><i class="fa-solid fa-graduation-cap w-5 text-slate-400"></i> Teknik Informatika</p>
                                <p><i class="fa-solid fa-envelope w-5 text-slate-400"></i> qonita.zahida@mahasiswa.ac.id</p>
                                <p><i class="fa-solid fa-phone w-5 text-slate-400"></i> +62 812-3456-7890</p>
                            </div>
                        </div>

                        <!-- Edit Profile Form Right -->
                        <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-surface-border space-y-4">
                            <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-3">Ubah Informasi Profil</h3>
                            <form class="space-y-4 text-xs">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                                        <input type="text" value="Qonita Husna Zahida" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-slate-700 mb-1">NIM</label>
                                        <input type="text" value="I43252605" disabled class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-500">
                                    </div>
                                </div>
                                <div>
                                    <label class="block font-semibold text-slate-700 mb-1">Email Mahasiswa</label>
                                    <input type="email" value="qonita.zahida@mahasiswa.ac.id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                                </div>
                                <div class="pt-2">
                                    <button type="button" class="px-4 py-2 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <!-- Modal 1: Activate Internship -->
    <div id="modal-activate-internship" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-surface-border space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Aktifkan Magang Resmi</h3>
                <button onclick="closeModal('modal-activate-internship')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Perusahaan Magang</label>
                    <input type="text" value="PT Telkom Indonesia" disabled class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-slate-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" value="2026-11-01" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" value="2027-02-28" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Pilih Dosen Pembimbing</label>
                    <select class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                        <option>Dr. Ir. Ahmad Dahlan, M.T. (NIDN: 041208801)</option>
                        <option>Prof. Budi Santoso, Ph.D. (NIDN: 041505782)</option>
                    </select>
                </div>

                <div class="pt-3 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeModal('modal-activate-internship')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                    <button type="button" onclick="activateInternshipSim(); closeModal('modal-activate-internship')" class="px-4 py-2 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700">Simpan & Aktifkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Add Logbook -->
    <div id="modal-add-logbook" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-surface-border space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-sm text-slate-900">Isi Logbook Harian</h3>
                <button onclick="closeModal('modal-add-logbook')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <form class="space-y-4 text-xs">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Tanggal Kegiatan</label>
                        <input type="date" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Durasi (Jam)</label>
                        <input type="number" placeholder="misal: 8" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Deskripsi Kegiatan</label>
                    <textarea rows="4" placeholder="Jelaskan secara rinci kegiatan magang yang kamu kerjakan hari ini..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-brand-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-between">
                    <button type="button" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200">Simpan Draft</button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeModal('modal-add-logbook')" class="px-4 py-2 border border-slate-200 rounded-xl text-slate-600 font-semibold hover:bg-slate-50">Batal</button>
                        <button type="button" onclick="closeModal('modal-add-logbook')" class="px-4 py-2 bg-brand-600 text-white rounded-xl font-semibold hover:bg-brand-700">Submit Logbook</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentScenario = 'pre'; // 'pre' or 'during'

        // Single Page Navigation Logic
        function switchTab(tabName) {
            const tabs = ['dashboard', 'explore', 'applications', 'documents', 'my-internship', 'logbook', 'calendar', 'profile'];
            
            tabs.forEach(t => {
                const el = document.getElementById(`tab-${t}`);
                const nav = document.getElementById(`nav-${t}`);
                
                if (el) el.classList.add('hidden');
                if (nav) {
                    nav.className = "w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-slate-600 hover:text-brand-600 hover:bg-slate-50 font-medium transition-all";
                }
            });

            const activeTab = document.getElementById(`tab-${tabName}`);
            const activeNav = document.getElementById(`nav-${tabName}`);

            if (activeTab) activeTab.classList.remove('hidden');
            if (activeNav) {
                activeNav.className = "w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs text-brand-600 bg-brand-50 font-semibold border-l-4 border-brand-600 transition-all";
            }
        }

        // Apply from Explore to Tracker
        function quickApply(companyName, position) {
            switchTab('applications');
            const tbody = document.getElementById('application-table-body');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td class="py-4 px-4 font-semibold text-slate-800">${companyName}</td>
                <td class="py-4 px-4 text-slate-600">${position}</td>
                <td class="py-4 px-4 text-slate-500">Hari ini</td>
                <td class="py-4 px-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Applied</span>
                </td>
                <td class="py-4 px-4 text-right">
                    <button class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-pen-to-square"></i> Update</button>
                </td>
            `;
            tbody.prepend(newRow);
        }

        // Filter Companies
        function filterCompanyCards() {
            const searchVal = document.getElementById('input-search-company').value.toLowerCase();
            const industryVal = document.getElementById('select-filter-industry').value;
            const cards = document.querySelectorAll('.company-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name').toLowerCase();
                const industry = card.getAttribute('data-industry');

                const matchesSearch = name.includes(searchVal);
                const matchesIndustry = !industryVal || industry === industryVal;

                if (matchesSearch && matchesIndustry) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        }

        // Scenario Switcher Logic (Demo Tool)
        function setScenario(stage) {
            currentScenario = stage;

            const btnPre = document.getElementById('btn-scenario-pre');
            const btnDuring = document.getElementById('btn-scenario-during');

            const statusBadge = document.getElementById('dashboard-status-badge');
            const statMagangStatus = document.getElementById('stat-magang-status');
            const statMagangLabel = document.getElementById('stat-magang-label');
            const statMagangDetail = document.getElementById('stat-magang-detail');
            const statLogbookHours = document.getElementById('stat-logbook-hours');
            const statLogbookCount = document.getElementById('stat-logbook-count');

            const lockBadge1 = document.getElementById('badge-lock-my-internship');
            const lockBadge2 = document.getElementById('badge-lock-logbook');

            const myInternshipLocked = document.getElementById('banner-my-internship-locked');
            const myInternshipActive = document.getElementById('content-my-internship-active');
            const logbookLocked = document.getElementById('banner-logbook-locked');
            const logbookActive = document.getElementById('content-logbook-active');

            if (stage === 'pre') {
                btnPre.className = "px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white text-brand-600 shadow-xs transition-all";
                btnDuring.className = "px-2.5 py-1 text-[11px] font-semibold rounded-lg text-slate-600 hover:text-slate-900 transition-all";

                statusBadge.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold";
                statusBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Status: Pre-Internship`;

                statMagangStatus.innerText = "1";
                statMagangLabel.innerText = "Diterima";
                statMagangDetail.innerText = "PT Telkom Indonesia";
                statLogbookHours.innerText = "0";
                statLogbookCount.innerText = "Belum Mulai Magang";

                lockBadge1.classList.remove('hidden');
                lockBadge2.classList.remove('hidden');

                myInternshipLocked.classList.remove('hidden');
                myInternshipActive.classList.add('hidden');
                logbookLocked.classList.remove('hidden');
                logbookActive.classList.add('hidden');
            } else {
                btnDuring.className = "px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-white text-brand-600 shadow-xs transition-all";
                btnPre.className = "px-2.5 py-1 text-[11px] font-semibold rounded-lg text-slate-600 hover:text-slate-900 transition-all";

                statusBadge.className = "inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold";
                statusBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Status: Magang Aktif`;

                statMagangStatus.innerText = "Aktif";
                statMagangLabel.innerText = "Berjalan";
                statMagangDetail.innerText = "PT Telkom Indonesia";
                statLogbookHours.innerText = "16";
                statLogbookCount.innerText = "2 Kegiatan Logbook";

                lockBadge1.classList.add('hidden');
                lockBadge2.classList.add('hidden');

                myInternshipLocked.classList.add('hidden');
                myInternshipActive.classList.remove('hidden');
                logbookLocked.classList.add('hidden');
                logbookActive.classList.remove('hidden');
            }
        }

        function activateInternshipSim() {
            setScenario('during');
            switchTab('my-internship');
        }

        // Modal Triggers
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }

        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function toggleMobileSidebar() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('fixed');
            sidebar.classList.toggle('inset-y-0');
            sidebar.classList.toggle('left-0');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('shadow-2xl');
        }

        // Initialize state
        setScenario('pre');
    </script>
</body>
</html>