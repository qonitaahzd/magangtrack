<?php
/**
 * MAGANGTRACK - Shared Header Component
 * Lokasi berkas: magangtrack/includes/header.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Path relatif yang presisi dari direktori includes/
require_once __DIR__ . '/../auth/auth_middleware.php';
require_once __DIR__ . '/../config/database.php';

// Pastikan pengguna sudah terautentikasi untuk halaman yang memanggil header ini
requireLogin();

// Cek Status Magang khusus untuk Mahasiswa
$isInternshipScheduled = false;
$isInternshipActive    = false;

if (($_SESSION['role'] ?? '') === 'mahasiswa' && !empty($_SESSION['user_id'])) {
    try {
        $dbHeader = getDBConnection();
        $stmtHeader = $dbHeader->prepare("
            SELECT status_magang 
            FROM internships 
            WHERE user_id = :user_id AND status_magang IN ('Terjadwal', 'Aktif') 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmtHeader->execute([':user_id' => $_SESSION['user_id']]);
        $internRecord = $stmtHeader->fetch();

        if ($internRecord) {
            $status = $internRecord['status_magang'];
            $isInternshipScheduled = ($status === 'Terjadwal' || $status === 'Aktif');
            $isInternshipActive    = ($status === 'Aktif');
        }
    } catch (PDOException $e) {
        $isInternshipScheduled = false;
        $isInternshipActive    = false;
    }
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAGANGTRACK - Sistem Manajemen & Monitoring Magang</title>
    
    <!-- Google Fonts & Font Awesome Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'] 
                    },
                    colors: { 
                        brand: { 
                            50: '#eef2ff', 
                            100: '#e0e7ff',
                            500: '#6366f1', 
                            600: '#4f46e5', 
                            700: '#4338ca' 
                        }, 
                        surface: { 
                            bg: '#f8fafc', 
                            border: '#e2e8f0' 
                        } 
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    
    <script>
        // Deklarasi dini toggleSidebar untuk mencegah ReferenceError saat mobile click
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const btnToggle = document.getElementById('btn-hamburger-global');

            if (sidebar) {
                const isHidden = sidebar.classList.contains('-translate-x-full');
                sidebar.classList.toggle('-translate-x-full');
                if (btnToggle) {
                    btnToggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                }
            }
            if (backdrop) {
                backdrop.classList.toggle('hidden');
            }
        }

        // Aksesibilitas: Menutup sidebar mobile saat menekan tombol Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const sidebar = document.getElementById('main-sidebar');
                if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
                    toggleSidebar();
                }
            }
        });
    </script>
</head>
<body class="h-screen overflow-hidden p-2 sm:p-3 md:p-5">
    <div class="flex h-full w-full bg-surface-bg rounded-2xl shadow-sm border border-surface-border overflow-hidden relative">
        <!-- Tombol Hamburger Mobile Global -->
        <button type="button" id="btn-hamburger-global" onclick="toggleSidebar()" aria-controls="main-sidebar" aria-expanded="false" aria-label="Buka Menu Navigasi" class="md:hidden absolute top-4 left-4 z-30 p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-brand-600 shadow-xs focus:outline-none" title="Buka Menu">
            <i class="fa-solid fa-bars text-base"></i>
        </button>