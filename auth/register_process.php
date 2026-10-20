<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = trim($_POST['nama'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $nim         = trim($_POST['nim'] ?? '');
    $prodi       = trim($_POST['prodi'] ?? 'Teknik Informatika');
    $password    = trim($_POST['password'] ?? '');
    $confirm_pass = trim($_POST['confirm_password'] ?? '');

    if (empty($nama) || empty($email) || empty($nim) || empty($password)) {
        $_SESSION['error'] = 'Seluruh kolom wajib diisi!';
        header('Location: /magangtrack/register.php');
        exit();
    }

    if ($password !== $confirm_pass) {
        $_SESSION['error'] = 'Konfirmasi password tidak cocok!';
        header('Location: /magangtrack/register.php');
        exit();
    }

    try {
        $db = getDBConnection();

        // Cek ketersediaan Email atau NIM
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = :email OR nim_nidn = :nim LIMIT 1");
        $checkStmt->execute([':email' => $email, ':nim' => $nim]);
        
        if ($checkStmt->fetch()) {
            $_SESSION['error'] = 'Email atau NIM sudah terdaftar pada sistem!';
            header('Location: /magangtrack/register.php');
            exit();
        }

        // Hash Password menggunakan default BCRYPT
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Registrasi KHUSUS Mahasiswa
        $insertStmt = $db->prepare("
            INSERT INTO users (nama, email, password, role, nim_nidn, program_studi) 
            VALUES (:nama, :email, :password, 'mahasiswa', :nim, :prodi)
        ");
        
        $insertStmt->execute([
            ':nama'     => $nama,
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':nim'      => $nim,
            ':prodi'    => $prodi
        ]);

        $_SESSION['success'] = 'Registrasi berhasil! Silakan login menggunakan akun Anda.';
        header('Location: /magangtrack/login.php');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        header('Location: /magangtrack/register.php');
        exit();
    }
} else {
    header('Location: /magangtrack/register.php');
    exit();
}