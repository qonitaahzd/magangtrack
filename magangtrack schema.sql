-- ====================================================================
-- SKRIP DATABASE MAGANGTRACK (FINAL PRODUCTION & DEMO SEEDER)
-- Kompatibel dengan MySQL 5.7+ / MariaDB 10.2+ (Laragon Environment)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `magangtrack` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `magangtrack`;

-- --------------------------------------------------------------------
-- 1. TABEL: users
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('mahasiswa', 'dosen') NOT NULL,
  `nim_nidn` VARCHAR(30) NOT NULL UNIQUE,
  `program_studi` VARCHAR(100) DEFAULT 'Teknik Informatika',
  `no_hp` VARCHAR(20) DEFAULT NULL,
  `avatar` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. TABEL: companies (Katalog Referensi Perusahaan Simulasi)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_perusahaan` VARCHAR(100) NOT NULL,
  `sektor_industri` VARCHAR(100) NOT NULL,
  `lokasi` VARCHAR(100) NOT NULL,
  `kontak_email` VARCHAR(100) DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `created_by_user_id` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_companies_user` 
    FOREIGN KEY (`created_by_user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. TABEL: applications (Application Tracker)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `company_id` INT DEFAULT NULL,
  `nama_perusahaan_snapshot` VARCHAR(100) NOT NULL,
  `posisi_magang` VARCHAR(100) NOT NULL,
  `status` ENUM('Applied', 'Interview Scheduled', 'Accepted', 'Rejected') DEFAULT 'Applied',
  `tgl_apply` DATE NOT NULL,
  `catatan` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_applications_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_applications_company` 
    FOREIGN KEY (`company_id`) 
    REFERENCES `companies` (`id`) 
    ON DELETE SET NULL 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. TABEL: document_checklists
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `document_checklists` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `nama_dokumen` VARCHAR(150) NOT NULL,
  `deskripsi` VARCHAR(255) DEFAULT NULL,
  `is_completed` TINYINT(1) DEFAULT 0,
  CONSTRAINT `fk_doc_checklists_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. TABEL: deadlines (Akses sejak Before Internship)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `deadlines` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `judul_agenda` VARCHAR(150) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `tanggal_deadline` DATE NOT NULL,
  `kategori` ENUM('Berkas', 'Interview', 'Bimbingan', 'Laporan') DEFAULT 'Berkas',
  CONSTRAINT `fk_deadlines_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. TABEL: internships (Aturan Maksimal 1 Magang Terjadwal/Aktif)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `internships` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `application_id` INT NOT NULL,
  `dosen_id` INT NOT NULL,
  `tgl_mulai` DATE NOT NULL,
  `tgl_selesai` DATE NOT NULL,
  `target_jam` INT DEFAULT 600,
  `status_magang` ENUM('Terjadwal', 'Aktif', 'Selesai') DEFAULT 'Terjadwal',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  -- GENERATED COLUMN: Menghasilkan user_id HANYA JIKA status = Terjadwal ATAU Aktif
  `running_user_id` INT GENERATED ALWAYS AS (
    IF(`status_magang` IN ('Terjadwal', 'Aktif'), `user_id`, NULL)
  ) VIRTUAL,

  -- UNIQUE INDEX: Menjamin 1 mahasiswa hanya punya 1 nilai running_user_id non-null
  UNIQUE KEY `uk_running_internship_per_user` (`running_user_id`),

  CONSTRAINT `fk_internships_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_internships_application` 
    FOREIGN KEY (`application_id`) 
    REFERENCES `applications` (`id`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE,
  CONSTRAINT `fk_internships_dosen` 
    FOREIGN KEY (`dosen_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE RESTRICT 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. TABEL: logbooks
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `logbooks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `internship_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `kegiatan` TEXT NOT NULL,
  `durasi_jam` INT NOT NULL,
  `status_persetujuan` ENUM('Draft', 'Submitted', 'Approved', 'Revision Required') DEFAULT 'Draft',
  `catatan_dosen` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_logbooks_internship` 
    FOREIGN KEY (`internship_id`) 
    REFERENCES `internships` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- SEED DATA DEMO SIMULASI MAGANGTRACK
-- ====================================================================

-- 1. Akun Pengguna Demo
-- Catatan Password Hash: Standard PHP password_hash('password123', PASSWORD_BCRYPT)
-- Hash bcrypt tervalidasi untuk password demo: password123
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `nim_nidn`, `program_studi`, `no_hp`) VALUES
(1, 'Qonita Husna Zahida', 'qonita.zahida@mahasiswa.ac.id', '$2y$12$SAkceoYLa.iI2nZqk5VReuH0U70qvDcf/K7z.Sx.kHMTPCTMhQEl6', 'mahasiswa', 'I43252605', 'Teknik Informatika', '081234567890'),
(2, 'Siti Sarah Nurhaliza', 'sarah.nurhaliza@mahasiswa.ac.id', '$2y$12$SAkceoYLa.iI2nZqk5VReuH0U70qvDcf/K7z.Sx.kHMTPCTMhQEl6', 'mahasiswa', '2201010042', 'Teknik Informatika', '081298765432'),
(3, 'Dr. Ir. Ahmad Dahlan, M.T.', 'ahmad.dahlan@dosen.ac.id', '$2y$12$SAkceoYLa.iI2nZqk5VReuH0U70qvDcf/K7z.Sx.kHMTPCTMhQEl6', 'dosen', '041208801', 'Teknik Informatika', '081987654321');

-- 2. Katalog 5 Perusahaan Referensi Simulasi (is_verified = 0)
INSERT INTO `companies` (`id`, `nama_perusahaan`, `sektor_industri`, `lokasi`, `kontak_email`, `is_verified`, `created_by_user_id`) VALUES
(1, 'PT Telkom Indonesia', 'Telekomunikasi & Digital Platform', 'Jakarta Selatan / Remote', 'info@telkom.co.id', 0, NULL),
(2, 'PT PLN (Persero)', 'Ketenagalisrikan & Energetika', 'Jakarta Pusat / Hybrid', 'info@pln.co.id', 0, NULL),
(3, 'PT Pertamina (Persero)', 'Minyak, Gas, & Energi Terbarukan', 'Jakarta Pusat / Onsite', 'info@pertamina.com', 0, NULL),
(4, 'Bank BRI', 'Perbankan & Financial Technology', 'Jakarta Pusat / Onsite', 'info@bri.co.id', 0, NULL),
(5, 'GoTo Group', 'E-Commerce & Digital Services', 'Jakarta Selatan / Hybrid', 'info@goto.com', 0, NULL);

-- 3. Document Checklists Untuk Qonita (Before Internship Demo)
INSERT INTO `document_checklists` (`user_id`, `nama_dokumen`, `deskripsi`, `is_completed`) VALUES
(1, 'CV Terbaru (Curriculum Vitae)', 'Format ATS Friendly diperbarui dengan portofolio semester 5', 1),
(1, 'Transkrip Nilai Akademik', 'Transkrip nilai resmi dari Portal Akademik Kampus', 1),
(1, 'Portofolio Design & Code', 'Tautan GitHub dan Figma project', 1),
(1, 'Surat Pengantar Magang Kampus', 'Menunggu TTD dari Ketua Program Studi', 0);

-- 4. Document Checklists Untuk Sarah (During Internship Demo)
INSERT INTO `document_checklists` (`user_id`, `nama_dokumen`, `deskripsi`, `is_completed`) VALUES
(2, 'CV Terbaru (Curriculum Vitae)', 'CV format ATS Friendly', 1),
(2, 'Transkrip Nilai Akademik', 'Transkrip resmi disetujui', 1),
(2, 'Portofolio Design & Code', 'Link portofolio UI/UX', 1),
(2, 'Surat Pengantar Magang Kampus', 'Surat pengantar terbit', 1);

-- 5. Deadlines & Agenda Demo
INSERT INTO `deadlines` (`user_id`, `judul_agenda`, `keterangan`, `tanggal_deadline`, `kategori`) VALUES
(1, 'Submit Surat Pengantar Kampus', 'Batas akhir kelengkapan berkas administrasi', '2026-10-24', 'Berkas'),
(1, 'Interview PT Telkom Indonesia', 'Wawancara teknis via Google Meet pukul 10:00 WIB', '2026-10-28', 'Interview'),
(2, 'Bimbingan Logbook Pekan 1', 'Konsultasi jurnal harian bersama Dr. Ahmad Dahlan', '2026-10-25', 'Bimbingan');

-- 6. Lamaran Magang Demo
-- Qonita: Memiliki lamaran dengan status beragam di Before Internship
INSERT INTO `applications` (`id`, `user_id`, `company_id`, `nama_perusahaan_snapshot`, `posisi_magang`, `status`, `tgl_apply`, `catatan`) VALUES
(1, 1, 1, 'PT Telkom Indonesia', 'Frontend Developer Intern', 'Interview Scheduled', '2026-10-12', 'Interview tanggal 28 Okt 2026'),
(2, 1, 5, 'GoTo Group', 'UI/UX Designer Intern', 'Applied', '2026-10-10', 'Menunggu kabar HR'),
(3, 1, 4, 'Bank BRI', 'Backend Developer Intern', 'Rejected', '2026-10-01', 'Kualifikasi belum sesuai'),
-- Sarah: Lamaran yang sudah Accepted dan Diaktifkan
(4, 2, 1, 'PT Telkom Indonesia', 'Software Engineer Intern', 'Accepted', '2026-09-15', 'Diterima resmi melalui surat penerimaan');

-- 7. Magang Aktif Demo (Sarah Nurhaliza - During Internship)
INSERT INTO `internships` (`id`, `user_id`, `application_id`, `dosen_id`, `tgl_mulai`, `tgl_selesai`, `target_jam`, `status_magang`) VALUES
(1, 2, 4, 3, '2026-10-01', '2027-01-31', 600, 'Aktif');

-- 8. Logbook Harian Demo (Sarah Nurhaliza - During Internship)
INSERT INTO `logbooks` (`internship_id`, `tanggal`, `kegiatan`, `durasi_jam`, `status_persetujuan`, `catatan_dosen`) VALUES
(1, '2026-10-19', 'Onboarding tim engineering dan setting environment pengembangan Vue.js / PHP.', 8, 'Approved', 'Bagus, tingkatkan koordinasi dengan mentor lapangan.'),
(1, '2026-10-20', 'Slicing UI dashboard internal perusahaan dan integrasi API RESTful dasar.', 8, 'Submitted', NULL);