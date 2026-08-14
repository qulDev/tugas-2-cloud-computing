# Kos Qullah — Aplikasi Manajemen Kos-Kosan

Tugas 2 Cloud Computing — **Platform as a Service Berbasis Podman** (M. Rizqullah / `qullah`).
Stack: **PHP 8 (procedural) + MariaDB 10 + Apache + Podman Compose**, Bootstrap 5 via CDN.

---

## Fitur

| Modul | Fitur |
|-------|-------|
| **Publik** | Daftar kos (table 9 kolom + card view), pencarian & filter, detail kos + daftar penyewa (JOIN) |
| **Autentikasi** | Login admin (bcrypt), session, proteksi semua halaman `/admin/*` |
| **CRUD Kos** (Lapis A) | Tambah/Edit/Hapus + upload foto (BR-3: maks 2MB JPG/PNG/WebP), BR-4 tolak hapus kos berpenyewa |
| **Penyewa** | CRUD + dropdown kos (FK), daftar JOIN nama kos |
| **Keuangan** (B5) | CRUD kategori, pendapatan, pengeluaran; laporan bulanan (stat card + breakdown per kategori + chart Chart.js), BR-9 tolak hapus kategori terpakai |

---

## Struktur Proyek

```
uas/
├── compose.yml                 # 3 services + volume + depends_on (Soal 5)
├── db_kos.sql                  # DDL 6 tabel + FK + seed data (Soal 2)
├── php-apache.Containerfile    # PHP 8 + Apache + ext mysqli + nano (Soal 3)
├── mariadb.Containerfile       # MariaDB 10 + auto-import SQL (Soal 4)
└── www/                        # bind mount -> /var/www/html (Soal 5a)
    ├── index.php  detail.php  login.php  logout.php
    ├── koneksi.php  auth.php  flash.php  partials.php  style.css
    ├── uploads/default.jpg
    └── admin/                  # area terproteksi (wajib login)
        ├── dashboard.php  tambah_kos.php  edit_kos.php  hapus_kos.php
        ├── penyewa.php  tambah_penyewa.php  edit_penyewa.php  hapus_penyewa.php
        ├── kategori.php  tambah_kategori.php  edit_kategori.php  hapus_kategori.php
        ├── pendapatan.php  tambah_pendapatan.php  edit_pendapatan.php  hapus_pendapatan.php
        ├── pengeluaran.php tambah_pengeluaran.php edit_pengeluaran.php hapus_pengeluaran.php
        └── laporan.php
```

---

## Cara Menjalankan

```bash
# 1. Build & start 3 container (MariaDB jalan pertama via depends_on)
podman compose up -d --build

# 2. Cek status
podman ps        # 3 container Up: mariadb-qullah, php-apache-qullah, phpmyadmin-qullah

# 3. Akses
#   Aplikasi  -> http://localhost:8000
#   phpMyAdmin -> http://localhost:8001   (login: kos_user / kos_pass123)
#   Admin app -> http://localhost:8000/login.php  (login: admin / admin123)

# 4. Stop & hapus container (volume DB tetap)
podman compose down

# 4b. Reset total (ikut hapus volume DB) — hati-hati, data hilang
podman compose down -v
```

---

## Kredensial (seed)

| Layanan | Username | Password |
|---------|----------|----------|
| Admin aplikasi | `admin` | `admin123` |
| phpMyAdmin / DB | `kos_user` | `kos_pass123` |
| Root DB | `root` | `rootpass123` |

---

## Pemenuhan 11 Ketentuan Soal

| Soal | Ketentuan | Bukti |
|------|-----------|-------|
| 1 | Web CRUD PHP + MariaDB | `www/` (CRUD kos, penyewa, keuangan) |
| 2 | File SQL (DDL + DML min 2 record) | `db_kos.sql` (6 tabel, seed: admin 1, kos 2, penyewa 2, kategori 10, pendapatan 2, pengeluaran 2) |
| 3 | php-apache.Containerfile (mysqli + nano) | `RUN docker-php-ext-install mysqli` + `apt-get install -y nano` |
| 4 | mariadb.Containerfile (auto-import) | `COPY db_kos.sql /docker-entrypoint-initdb.d/` |
| 5 | compose.yml (3 services + volume + depends_on) | `compose.yml` |
| 6 | Build & run | `podman compose up -d --build` |
| 7 | Verify container | `podman ps -a` |
| 8 | phpMyAdmin verify | `http://localhost:8001` |
| 9 | Homepage tampil seluruh data tabel | `index.php` default table view 9 kolom |
| 10 | CRUD test | login admin -> tambah/edit/hapus kos & penyewa |
| 11 | Stop container | `podman compose down` |

---

## Catatan Teknis

- **Koneksi DB** memakai nama service `mariadb-qullah` sebagai host (bukan `localhost`) — wajib di dalam Podman Compose network.
- **FK** semua `ON DELETE RESTRICT ON UPDATE CASCADE` + pre-check PHP (BR-4 hapus kos, BR-9 hapus kategori).
- **Upload foto** disimpan di `www/uploads/` (tercakup dalam bind mount `./www:/var/www/html`).
- **Tanpa emoji dekoratif** — semua ikon pakai Bootstrap Icons (`<i class="bi bi-*">`).
- **Aksen warna** `--primary: #0f5132` (hijau gelap) di `www/style.css`.

&copy; 2026 M. Rizqullah — Tugas 2 Cloud Computing
