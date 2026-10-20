<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Memastikan pengguna sudah login
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /magangtrack/login.php');
        exit();
    }
}

/**
 * Memastikan pengguna memiliki role tertentu (misal: 'mahasiswa' atau 'dosen')
 */
function requireRole($expectedRole) {
    requireLogin();
    if ($_SESSION['role'] !== $expectedRole) {
        // Redirect ke dashboard yang sesuai jika mengakses role lain
        if ($_SESSION['role'] === 'dosen') {
            header('Location: /magangtrack/dosen/dashboard.php');
        } else {
            header('Location: /magangtrack/dashboard.php');
        }
        exit();
    }
}

/**
 * Mencegah pengguna yang sudah login untuk kembali ke halaman login/register
 */
function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        if ($_SESSION['role'] === 'dosen') {
            header('Location: /magangtrack/dosen/dashboard.php');
        } else {
            header('Location: /magangtrack/dashboard.php');
        }
        exit();
    }
}