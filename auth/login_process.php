<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Email dan password wajib diisi!';
        header('Location: /magangtrack/login.php');
        exit();
    }

    try {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT id, nama, email, password, role, nim_nidn FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Re-generate ID session untuk mencegah session fixation attacks
            session_regenerate_id(true);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['email']    = $user['email'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['nim_nidn'] = $user['nim_nidn'];

            // Redirect sesuai role pengguna
            if ($user['role'] === 'dosen') {
                header('Location: /magangtrack/dosen/dashboard.php');
            } else {
                header('Location: /magangtrack/dashboard.php');
            }
            exit();
        } else {
            $_SESSION['error'] = 'Email atau password tidak sesuai!';
            header('Location: /magangtrack/login.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        header('Location: /magangtrack/login.php');
        exit();
    }
} else {
    header('Location: /magangtrack/login.php');
    exit();
}