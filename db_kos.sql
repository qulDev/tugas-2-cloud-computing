-- ============================================================
-- db_kos.sql - Database Aplikasi Manajemen Kos-Kosan
-- Tugas 2 Cloud Computing - M. Rizqullah (qullah)
-- Auto-import oleh container mariadb-qullah saat init pertama.
-- ============================================================

CREATE DATABASE IF NOT EXISTS db_kos
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_kos;

-- ------------------------------------------------------------
-- Tabel admin (autentikasi - B1)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nama_lengkap  VARCHAR(100) DEFAULT NULL,
  created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel kos (entitas utama - Soal 2)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kos (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nama_kos        VARCHAR(100) NOT NULL,
  alamat          TEXT NOT NULL,
  tipe_kamar      ENUM('Putra','Putri','Campur') NOT NULL,
  harga_per_bulan INT NOT NULL,
  jumlah_kamar    INT NOT NULL,
  status          ENUM('Tersedia','Penuh') DEFAULT 'Tersedia',
  foto            VARCHAR(255) DEFAULT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel kategori (master data keuangan - B5)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS kategori (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nama       VARCHAR(50) NOT NULL,
  tipe       ENUM('Pendapatan','Pengeluaran') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel penyewa (B4) - FK ke kos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS penyewa (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(100) NOT NULL,
  no_hp       VARCHAR(20)  NOT NULL,
  email       VARCHAR(100) DEFAULT NULL,
  kos_id      INT NOT NULL,
  tgl_masuk   DATE NOT NULL,
  status_sewa ENUM('Aktif','Selesai') DEFAULT 'Aktif',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_penyewa_kos FOREIGN KEY (kos_id)
    REFERENCES kos(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel pendapatan (B5) - FK ke kos + kategori
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pendapatan (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  kos_id      INT NOT NULL,
  kategori_id INT NOT NULL,
  tanggal     DATE NOT NULL,
  jumlah      INT NOT NULL,
  keterangan  TEXT DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pendapatan_kos      FOREIGN KEY (kos_id)      REFERENCES kos(id)      ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pendapatan_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CHECK (jumlah > 0)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel pengeluaran (B5) - FK ke kos + kategori
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS pengeluaran (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  kos_id      INT NOT NULL,
  kategori_id INT NOT NULL,
  tanggal     DATE NOT NULL,
  jumlah      INT NOT NULL,
  keterangan  TEXT DEFAULT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pengeluaran_kos      FOREIGN KEY (kos_id)      REFERENCES kos(id)      ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pengeluaran_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CHECK (jumlah > 0)
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA (minimal 2 record per tabel sesuai Soal 2)
-- ============================================================

-- Admin: username=admin password=admin123 (bcrypt)
INSERT INTO admin (username, password_hash, nama_lengkap) VALUES
('admin', '$2b$10$PjxvhDIVvyXwnLDjAtHL5.R64AiQiIjm5yra2R145pe8lXuTu8nge', 'Administrator Kos');

INSERT INTO kos (nama_kos, alamat, tipe_kamar, harga_per_bulan, jumlah_kamar, status, foto) VALUES
('Kos Melati Jaya',   'Jl. Merdeka No.5, Mataram',       'Putri', 500000, 10, 'Tersedia', 'uploads/seed_melati.jpg'),
('Kos Sentosa Abadi', 'Jl. Diponegoro No.12, Cakranegara','Putra', 450000,  8, 'Tersedia', 'uploads/seed_sentosa.jpg');

INSERT INTO kategori (nama, tipe) VALUES
('Sewa',      'Pendapatan'),
('Deposit',   'Pendapatan'),
('Denda',     'Pendapatan'),
('Listrik',   'Pengeluaran'),
('Air',       'Pengeluaran'),
('Internet',  'Pengeluaran'),
('Sampah',    'Pengeluaran'),
('Perbaikan', 'Pengeluaran'),
('Gaji',      'Pengeluaran'),
('Lainnya',   'Pengeluaran');

INSERT INTO penyewa (nama, no_hp, email, kos_id, tgl_masuk, status_sewa) VALUES
('Andi Pratama',    '081234567890', 'andi@email.com', 1, '2026-07-01', 'Aktif'),
('Siti Rahmawati',  '082198765432', 'siti@email.com', 2, '2026-06-15', 'Aktif');

INSERT INTO pendapatan (kos_id, kategori_id, tanggal, jumlah, keterangan) VALUES
(1, 1, '2026-08-01', 500000, 'Sewa Andi bulan Agustus'),
(2, 1, '2026-08-03', 450000, 'Sewa Siti bulan Agustus');

INSERT INTO pengeluaran (kos_id, kategori_id, tanggal, jumlah, keterangan) VALUES
(1, 4, '2026-08-05', 150000, 'Bayar listrik Kos Melati'),
(2, 5, '2026-08-07',  80000, 'Bayar PDAM Kos Sentosa');
