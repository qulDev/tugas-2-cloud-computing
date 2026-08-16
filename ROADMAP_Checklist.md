# Roadmap Checklist — Manajemen Kos-Kosan

> Companion file ke `PRD_Manajemen_Kos_Kosan.md` v2.1.
> Fokus: **eksekusi coding**, bukan teori. Centang (`[x]`) tiap item yang selesai.
> Protocol: di akhir tiap Faze, panggil AI untuk **review** sebelum lanjut Faze berikutnya.

---

## KEPUTUSAN TEKNIS FINAL (lock-in, jangan diubah lagi)

| Aspek | Keputusan |
|-------|-----------|
| Stack | PHP 8 (procedural inline) + MariaDB 10 + Apache + Podman Compose |
| Styling | Bootstrap 5 via CDN (CDN `cdn.jsdelivr.net/npm/bootstrap@5.3.3`) + Bootstrap Icons (untuk ikon, BUKAN emoji) |
| Auth seed | Hardcode bcrypt hash di `db_kos.sql` (admin / admin123) |
| FK constraint | Explicit `ON DELETE RESTRICT ON UPDATE CASCADE` |
| Testing lokal | Podman Desktop (mirror Lab RHA) |
| Git | Private repo, password DB langsung di `compose.yml` |
| Branch | `main` saja (solo dev) |
| Volume | 1 bind mount `./www:/var/www/html` + named volume `db_data_qullah` |
| Nama suffix | `-qullah` (di semua service & container) |
| UI rules | **NO EMOJI dekoratif** — pakai Bootstrap Icons (`<i class="bi bi-*">`) atau text saja |
| Color palette | Netral + 1 aksen: pilih `#0f5132` (hijau gelap) ATAU `#1d3557` (navy) sebagai `--primary` |
| Format mata uang | `Rp 500.000` via `number_format($harga, 0, ',', '.')` |
| Modul keuangan (B5) | 3 tabel baru: kategori (1 tabel + tipe ENUM), pendapatan (FK kos + kategori), pengeluaran (FK kos + kategori). Seed 10 kategori default. Laporan bulanan dengan stat card + breakdown kategori. |

---

## FAZE 0 — SETUP REPO & TOOLING (~30 menit)

- [ ] Install Podman Desktop → https://podman-desktop.io/
- [ ] Verifikasi: `podman --version` keluar output
- [ ] Verifikasi: `podman compose version` keluar output
- [ ] Folder project `uas-qullah/` dibuat
- [ ] `git init` di dalam folder
- [ ] Buat **private repo** `uas-qullah` di GitHub/GitLab
- [ ] `git remote add origin <url-repo-anda>`
- [ ] Buat `.gitignore` (isi: `uploads/*`, `!uploads/default.jpg`, `.env`, `*.log`)
- [ ] Initial commit + push (`git push -u origin main`)

**Cek sukses Faze 0:**
- [ ] `podman --version` jalan
- [ ] `git log` ada 1 commit
- [ ] Repo di GitHub terlihat (empty tapi ada)

---

## FAZE 1 — FOUNDATION: SQL + CONTAINERFILE (~1.5 jam)

> Tujuan: Container jalan + DB auto-init dengan seed. **Belum ada PHP code.**

### 1.1 File `db_kos.sql`

- [ ] `CREATE DATABASE IF NOT EXISTS db_kos; USE db_kos;`
- [ ] Tabel `admin` (id, username UNIQUE, password_hash, nama_lengkap, created_at)
- [ ] Tabel `kos` (id, nama_kos, alamat TEXT, tipe_kamar ENUM, harga_per_bulan INT, jumlah_kamar INT, status ENUM default 'Tersedia', foto VARCHAR nullable, created_at)
- [ ] Tabel `penyewa` (id, nama, no_hp, email nullable, kos_id INT NOT NULL, tgl_masuk DATE, status_sewa ENUM default 'Aktif', created_at, **FK ke kos(id) ON DELETE RESTRICT ON UPDATE CASCADE**)
- [ ] Tabel `kategori` (id, nama VARCHAR(50), tipe ENUM('Pendapatan','Pengeluaran'), created_at) — **master data keuangan (B5)**
- [ ] Tabel `pendapatan` (id, kos_id INT NOT NULL, kategori_id INT NOT NULL, tanggal DATE, jumlah INT NOT NULL, keterangan TEXT nullable, created_at, **FK ke kos(id) + FK ke kategori(id)**, both ON DELETE RESTRICT ON UPDATE CASCADE)
- [ ] Tabel `pengeluaran` (id, kos_id INT NOT NULL, kategori_id INT NOT NULL, tanggal DATE, jumlah INT NOT NULL, keterangan TEXT nullable, created_at, **FK ke kos(id) + FK ke kategori(id)**, both ON DELETE RESTRICT ON UPDATE CASCADE)
- [ ] Seed `admin`: 1 record (username `admin`, bcrypt hash dari `admin123`)
- [ ] Seed `kos`: 2 record (Melati Jaya Putri 500rb, Sentosa Abadi Putra 450rb)
- [ ] Seed `penyewa`: 2 record dengan FK menyebar (kos_id=1 Andi, kos_id=2 Siti)
- [ ] Seed `kategori`: 10 record (3 Pendapatan: Sewa/Deposit/Denda; 7 Pengeluaran: Listrik/Air/Internet/Sampah/Perbaikan/Gaji/Lainnya)
- [ ] Seed `pendapatan`: 2 record (kos_id=1 Sewa 500rb 2026-08-01, kos_id=2 Sewa 450rb 2026-08-03)
- [ ] Seed `pengeluaran`: 2 record (kos_id=1 Listrik 150rb 2026-08-05, kos_id=2 Air 80rb 2026-08-07)

**Generate bcrypt hash admin123 (jalankan sekali):**
```bash
php -r "echo password_hash('admin123', PASSWORD_BCRYPT), PHP_EOL;"
```
→ Copy output ke SQL.

### 1.2 File `php-apache.Containerfile`

- [ ] `FROM php:8-apache`
- [ ] `RUN docker-php-ext-install mysqli` (Soal 3a)
- [ ] `RUN apt-get update && apt-get install -y nano && rm -rf /var/lib/apt/lists/*` (Soal 3b)
- [ ] (Opsional) `RUN a2enmod rewrite`

### 1.3 File `mariadb.Containerfile`

- [ ] `FROM mariadb:10`
- [ ] `COPY db_kos.sql /docker-entrypoint-initdb.d/` (Soal 4)
- [ ] (Opsional) charset utf8mb4

### 1.4 File `compose.yml`

- [ ] Service `mariadb-qullah`: build dari mariadb.Containerfile, port 3306:3306, env DB credentials, volume `db_data_qullah:/var/lib/mysql`
- [ ] Service `php-apache-qullah`: build dari php-apache.Containerfile, port 8000:80, bind mount `./www:/var/www/html`, `depends_on: [mariadb-qullah]`
- [ ] Service `phpmyadmin-qullah`: image `phpmyadmin/phpmyadmin`, port 8001:80, env PMA_HOST=mariadb-qullah, `depends_on: [mariadb-qullah]`
- [ ] Section `volumes:` declare `db_data_qullah:`

### 1.5 Folder `www/` + dummy

- [ ] Buat folder `www/`
- [ ] Buat `www/index.php` isi: `<?php echo "Hello from qullah!"; ?>`

### 1.6 TEST FAZE 1

- [ ] `podman compose up -d --build` (tunggu sampai done)
- [ ] `podman ps` → **3 container Up**
- [ ] `curl http://localhost:8000` → "Hello from qullah!"
- [ ] Browser `http://localhost:8001` → phpMyAdmin login
- [ ] Login `kos_user` / `kos_pass123` → masuk dashboard
- [ ] DB `db_kos` terlihat di sidebar
- [ ] Browse tabel `kos` → 2 record
- [ ] Browse tabel `penyewa` → 2 record
- [ ] Browse tabel `admin` → 1 record
- [ ] Browse tabel `kategori` → 10 record (3 Pendapatan + 7 Pengeluaran)
- [ ] Browse tabel `pendapatan` → 2 record
- [ ] Browse tabel `pengeluaran` → 2 record

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 2**

```bash
git add . && git commit -m "feat: foundation SQL + Containerfile + compose (Faze 1)" && git push
```

---

## FAZE 2 — DB CONNECTION & HOMEPAGE (~1 jam)

> Tujuan: Homepage tabel 9 kolom dari DB real (pemenuhan Soal 9).

### 2.1 `www/koneksi.php`

- [ ] `mysqli_connect('mariadb-qullah', 'kos_user', 'kos_pass123', 'db_kos')` — **host = nama service, BUKAN localhost!**
- [ ] Error handling: `die()` kalau gagal connect

### 2.2 `www/index.php` (rewrite dummy)

- [ ] `require 'koneksi.php';`
- [ ] Build query dinamis dengan filter (B2): `?q=&tipe=&status=&min=&max=` → WHERE clauses
- [ ] `SELECT * FROM kos ORDER BY id DESC`
- [ ] Include Bootstrap 5 CSS di `<head>`: `<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">`
- [ ] Include Bootstrap Icons di `<head>`: `<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">`
- [ ] Include `<link href="/style.css" rel="stylesheet">`
- [ ] Include Bootstrap JS bundle sebelum `</body>`: `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>`
- [ ] Search box + filter form di atas (pakai `form-control`, `form-select`)
- [ ] Tombol toggle [Table View] / [Card View] (ikon text only, no emoji)
- [ ] **TABLE VIEW (default, id="table-view")** — `<table class="table table-striped table-hover table-bordered align-middle">` wrap dalam `<div class="table-responsive">`; 9 kolom: id, nama_kos, alamat (truncate ~50 char + tooltip via `title=""`), tipe_kamar, harga_per_bulan (format `Rp 500.000`), jumlah_kamar, status (`<span class="badge bg-success rounded-pill">`), foto (thumbnail 50x50 `class="rounded"`), created_at (format tanggal)
- [ ] **CARD VIEW (hidden, id="card-view")** — `<div class="row row-cols-1 row-cols-md-3 g-4">` berisi `<div class="card h-100 shadow-sm">` per kos: foto (`card-img-top`), nama (`card-title`), harga (`card-text fw-bold`), status badge, link detail
- [ ] Foto: kalau NULL → pakai `uploads/default.jpg`
- [ ] JS inline untuk toggle (ganti `display:none`/`block` berdasarkan id, tanpa reload)

### 2.3 `www/uploads/default.jpg`

- [ ] Siapkan 1 image placeholder sederhana

### 2.4 `www/style.css` (foundation)

- [ ] Define CSS variables `:root` (lihat PRD 8.3): `--primary: #0f5132` (atau navy `#1d3557`, pilih satu), `--surface`, `--surface-muted`, `--text`, `--text-muted`, `--border`
- [ ] `body { background: var(--surface); color: var(--text); font-family: system-ui, -apple-system, sans-serif; }`
- [ ] `.navbar-dark { background: var(--primary) !important; }`
- [ ] `.btn-primary { background: var(--primary); border-color: var(--primary); }`
- [ ] `.btn-primary:hover { background: filter dark; }` (atau hardcoded hex lebih gelap)

### 2.5 TEST FAZE 2

- [ ] Homepage menampilkan **2 record kos** (Melati Jaya, Sentosa Abadi)
- [ ] Tabel punya **9 kolom**
- [ ] Toggle "Card View" → grid card muncul, table hilang
- [ ] Toggle balik "Table View" → tabel kembali
- [ ] Search "Melati" → cuma 1 record
- [ ] Filter tipe "Putri" → cuma Melati Jaya
- [ ] Foto tampil (default.jpg kalau NULL)
- [ ] Warna aksen (navbar/button) konsisten pakai `--primary`
- [ ] **Tidak ada emoji di UI** (hanya Bootstrap Icons atau text)

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 3**

```bash
git add . && git commit -m "feat: homepage tabel 9 kolom + card view + filter (Faze 2)" && git push
```

---

## FAZE 3 — AUTHENTICATION (~1 jam)

> Tujuan: Admin login, halaman admin terproteksi.

### 3.1 `www/auth.php`

- [ ] `session_start();`
- [ ] Function `check_admin()`: cek `$_SESSION['admin'] === true`, kalau belum → redirect ke `/login.php`

### 3.2 `www/login.php`

- [ ] Form: username + password
- [ ] POST handler: `SELECT password_hash FROM admin WHERE username=?`
- [ ] `password_verify($_POST['password'], $row['password_hash'])`
- [ ] Kalau cocok → `$_SESSION['admin'] = true` + `$_SESSION['username']` → redirect `admin/dashboard.php`
- [ ] Kalau salah → tampilkan pesan error "Username/password salah"

### 3.3 `www/logout.php`

- [ ] `session_start(); session_destroy(); header('Location: /index.php');`

### 3.4 `www/admin/dashboard.php` (dummy dulu)

- [ ] `require '../auth.php'; check_admin();` di paling atas
- [ ] Echo "Dashboard admin - selamat datang, qullah"

### 3.5 `www/flash.php` (helper flash message — akan dipakai mulai Faze 4)

- [ ] Function `set_flash($type, $msg)` → simpan `$_SESSION['flash'] = ['type'=>$type, 'msg'=>$msg]`
- [ ] Function `show_flash()` → jika ada `$_SESSION['flash']`, render Bootstrap alert (`alert-success` / `alert-danger` / `alert-warning`) lalu `unset()`
- [ ] Helper ini dipakai di semua redirect hasil CRUD untuk feedback user

### 3.5 TEST FAZE 3

- [ ] `http://localhost:8000/login.php` tampil form
- [ ] Login `admin` / `admin123` → redirect ke dashboard
- [ ] Login password salah (`admin` / `salah`) → tetap di login page + error
- [ ] Akses `admin/dashboard.php` TANPA login → redirect ke login
- [ ] Klik logout → balik ke homepage

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 4**

```bash
git add . && git commit -m "feat: auth login/logout/session (Faze 3)" && git push
```

---

## FAZE 4 — CRUD KOS (~2 jam) — LAPIS A INTI

> Tujuan: Pemenuhan Soal 1 (CRUD) + Soal 10 (verifikasi CRUD).

### 4.1 `www/admin/dashboard.php` (final)

- [ ] Tampilkan: jumlah total kos, jumlah total penyewa, jumlah kos Tersedia
- [ ] Link ke: Kelola Kos, Kelola Penyewa, Logout
- [ ] Tombol "Tambah Kos"

### 4.2 `www/admin/tambah_kos.php` (CREATE)

- [ ] `require '../auth.php'; check_admin();`
- [ ] Form: nama_kos, alamat (textarea), tipe_kamar (select), harga_per_bulan, jumlah_kamar, status, foto (input file)
- [ ] Validasi server-side: nama & alamat wajib, harga & jumlah > 0 (BR-6)
- [ ] Validasi upload foto (BR-3): max 2MB, format JPG/PNG/WebP
- [ ] Upload foto opsional → `move_uploaded_file()` ke `uploads/`
- [ ] Generate nama file unik (mis. `kos_<timestamp>.jpg`) untuk hindari overwrite
- [ ] `INSERT INTO kos (..., foto) VALUES (...)`
- [ ] Flash message sukses → redirect dashboard

### 4.3 `www/admin/edit_kos.php` (UPDATE)

- [ ] `?id=X` → `SELECT * FROM kos WHERE id=X` untuk pre-fill form
- [ ] Form sama seperti tambah, foto lama tampil sebagai preview
- [ ] Submit → `UPDATE kos SET ... WHERE id=X`
- [ ] Foto: kalau user upload baru → hapus file lama (kalau bukan default) + ganti path; kalau tidak upload → keep path lama

### 4.4 `www/admin/hapus_kos.php` (DELETE)

- [ ] `?id=X`
- [ ] **PRE-CHECK BR-4:** `SELECT COUNT(*) FROM penyewa WHERE kos_id=X`
- [ ] Kalau count > 0 → **TOLAK**, redirect + flash "Tidak bisa menghapus, masih ada N penyewa aktif. Hapus/pindahkan penyewa dulu."
- [ ] Kalau count = 0 → hapus file foto dari filesystem (kalau bukan default) → `DELETE FROM kos WHERE id=X` → flash sukses

### 4.5 TEST FAZE 4 (Soal 10 compliance)

- [ ] CREATE: tambah kos baru "Kos Test" + upload foto → muncul di homepage
- [ ] CREATE tanpa upload foto → tetap berhasil, foto = default.jpg
- [ ] UPDATE: edit "Kos Test" → data berubah di homepage
- [ ] UPDATE dengan ganti foto → foto baru tampil, file lama terhapus
- [ ] DELETE kos **kosong** (Kos Test, tanpa penyewa) → berhasil dihapus
- [ ] DELETE kos **dengan penyewa** (id=1 Melati Jaya yang dihuni Andi) → **DITOLAK**, pesan muncul
- [ ] Hapus Andi dulu → baru bisa hapus Melati Jaya

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 5**

```bash
git add . && git commit -m "feat: CRUD kos + upload foto + BR-4 (Faze 4)" && git push
```

---

## FAZE 5 — DETAIL KOS + CRUD PENYEWA (~1.5 jam)

> Tujuan: Fitur B3 (detail + JOIN) + B4 (manajemen penyewa).

### 5.1 `www/detail.php?id=X` (PUBLIC, nilai tambah JOIN)

- [ ] `SELECT * FROM kos WHERE id=X` → tampilkan info lengkap + foto besar
- [ ] **JOIN:** `SELECT * FROM penyewa WHERE kos_id=X ORDER BY tgl_masuk DESC`
- [ ] Tampilkan daftar penyewa (nama, no_hp, tgl_masuk, status_sewa)
- [ ] Edge case: kalau kos tidak punya penyewa → "Belum ada penyewa di kos ini"
- [ ] Tombol "Kembali ke daftar"

### 5.2 `www/admin/penyewa.php` (LIST)

- [ ] `SELECT p.*, k.nama_kos FROM penyewa p JOIN kos k ON p.kos_id=k.id`
- [ ] Tabel: nama, no_hp, email, kos (nama_kos dari JOIN), tgl_masuk, status, aksi (edit/hapus)
- [ ] Tombol "Tambah Penyewa"

### 5.3 `www/admin/tambah_penyewa.php` (CREATE)

- [ ] Form: nama, no_hp, email (opsional), kos_id (dropdown dari `SELECT * FROM kos`), tgl_masuk, status_sewa
- [ ] Validasi: nama & no_hp wajib
- [ ] `INSERT INTO penyewa (...) VALUES (...)`

### 5.4 `www/admin/edit_penyewa.php` (UPDATE)

- [ ] Pre-fill form dari `SELECT ... WHERE id=X`
- [ ] Submit → `UPDATE penyewa SET ... WHERE id=X`

### 5.5 `www/admin/hapus_penyewa.php` (DELETE)

- [ ] Konfirmasi JS `confirm()`
- [ ] `DELETE FROM penyewa WHERE id=X` (penyewa bebas dihapus, tidak ada dependensi)

### 5.6 TEST FAZE 5

- [ ] `detail.php?id=1` → info Melati Jaya + daftar "Andi Pratama"
- [ ] `detail.php?id=2` → info Sentosa + daftar "Siti Rahmawati"
- [ ] Tambah penyewa baru → muncul di `penyewa.php` dan di detail kos terkait
- [ ] Edit penyewa → data berubah
- [ ] Hapus penyewa → hilang dari daftar
- [ ] Dropdown kos di form penyewa terisi semua kos

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 6**

```bash
git add . && git commit -m "feat: detail kos JOIN + CRUD penyewa (Faze 5)" && git push
```

---

## FAZE 6 — MANAJEMEN KEUANGAN (B5) (~3 jam)

> Tujuan: Modul keuangan kos — kategori custom + pendapatan + pengeluaran + laporan bulanan.
> Modul ini paling kompleks karena melibatkan FK ke 2 tabel (kos + kategori) + agregasi SUM untuk laporan.

### 6.1 CRUD Kategori (master data)

> **Buat ini DULU sebelum pendapatan/pengeluaran** karena form transaksi butuh dropdown kategori.

#### `www/admin/kategori.php` (LIST)

- [ ] `require '../auth.php'; check_admin(); require '../flash.php';`
- [ ] `SELECT * FROM kategori ORDER BY tipe, nama`
- [ ] Tampilkan tabel: nama, tipe (badge: Pendapatan=`bg-success`, Pengeluaran=`bg-danger`), aksi edit/hapus
- [ ] Tombol "Tambah Kategori"
- [ ] Filter tipe (All / Pendapatan / Pengeluaran) via `?tipe=`

#### `www/admin/tambah_kategori.php` (CREATE)

- [ ] Form: nama (text), tipe (radio: Pendapatan / Pengeluaran)
- [ ] Validasi: nama tidak kosong, tipe valid
- [ ] `INSERT INTO kategori (nama, tipe) VALUES (...)`
- [ ] Flash sukses → redirect `kategori.php`

#### `www/admin/edit_kategori.php` (UPDATE)

- [ ] `?id=X` → pre-fill dari `SELECT ... WHERE id=X`
- [ ] Submit → `UPDATE kategori SET nama=?, tipe=? WHERE id=X`

#### `www/admin/hapus_kategori.php` (DELETE + BR-9 pre-check)

- [ ] **PRE-CHECK BR-9:** `SELECT COUNT(*) FROM pendapatan WHERE kategori_id=X` + `SELECT COUNT(*) FROM pengeluaran WHERE kategori_id=X`
- [ ] Kalau total > 0 → **TOLAK** dengan flash "Kategori ini masih dipakai N transaksi. Hapus/ubah transaksi tersebut dulu."
- [ ] Kalau total = 0 → `DELETE FROM kategori WHERE id=X` + flash sukses

### 6.2 CRUD Pendapatan

#### `www/admin/pendapatan.php` (LIST)

- [ ] JOIN query: `SELECT p.*, k.nama_kos, kat.nama AS kategori FROM pendapatan p JOIN kos k ON p.kos_id=k.id JOIN kategori kat ON p.kategori_id=kat.id ORDER BY p.tanggal DESC`
- [ ] Tabel: tanggal, kos (nama_kos), kategori, jumlah (format `Rp` + `text-end fw-bold`), keterangan, aksi edit/hapus
- [ ] Filter bulan + tahun via `?bulan=&tahun=` (default bulan/tahun saat ini)
- [ ] Tombol "Tambah Pendapatan"

#### `www/admin/tambah_pendapatan.php` (CREATE)

- [ ] Dropdown kos dari `SELECT id, nama_kos FROM kos ORDER BY nama_kos`
- [ ] Dropdown kategori dari `SELECT id, nama FROM kategori WHERE tipe='Pendapatan' ORDER BY nama` ← **filter tipe!**
- [ ] Input tanggal (default `date('Y-m-d')` hari ini), jumlah (number min=1), keterangan (textarea opsional)
- [ ] Validasi BR-8: jumlah > 0
- [ ] `INSERT INTO pendapatan (kos_id, kategori_id, tanggal, jumlah, keterangan) VALUES (...)`
- [ ] Flash sukses → redirect `pendapatan.php`

#### `www/admin/edit_pendapatan.php` (UPDATE)

- [ ] Pre-fill dari JOIN query
- [ ] Submit → `UPDATE pendapatan SET ... WHERE id=X`

#### `www/admin/hapus_pendapatan.php` (DELETE)

- [ ] `DELETE FROM pendapatan WHERE id=X` (bebas dihapus, tidak ada dependensi)
- [ ] Flash sukses

### 6.3 CRUD Pengeluaran

> **Struktur identik dengan pendapatan**, cuma filter kategori `WHERE tipe='Pengeluaran'`.

- [ ] `www/admin/pengeluaran.php` (LIST + filter bulan/tahun)
- [ ] `www/admin/tambah_pengeluaran.php` (CREATE, kategori filter Pengeluaran)
- [ ] `www/admin/edit_pengeluaran.php` (UPDATE)
- [ ] `www/admin/hapus_pengeluaran.php` (DELETE)

### 6.4 Laporan Keuangan Bulanan (`www/admin/laporan.php`)

> **Ini fitur paling impresif** — memperlihatkan pemahaman query agregasi (SUM, GROUP BY, JOIN).

- [ ] Filter: dropdown bulan (1-12) + tahun (2025, 2026, 2027) → `?bulan=08&tahun=2026` (default = bulan/tahun saat ini)
- [ ] **3 Stat Card**:
  - Total Pendapatan: `SELECT COALESCE(SUM(jumlah),0) FROM pendapatan WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?` → card `bg-success`
  - Total Pengeluaran: query sama di tabel `pengeluaran` → card `bg-danger`
  - Saldo: pendapatan - pengeluaran → card `bg-primary` (atau `bg-secondary` kalau negatif)
- [ ] **Breakdown Pendapatan per Kategori**:
  ```sql
  SELECT kat.nama, COALESCE(SUM(p.jumlah),0) AS total
  FROM kategori kat
  LEFT JOIN pendapatan p ON kat.id=p.kategori_id AND MONTH(p.tanggal)=? AND YEAR(p.tanggal)=?
  WHERE kat.tipe='Pendapatan'
  GROUP BY kat.id, kat.nama
  ORDER BY total DESC
  ```
  → tampilkan tabel (kategori, total, persentase)
- [ ] **Breakdown Pengeluaran per Kategori** (query sama, tipe='Pengeluaran')
- [ ] (Opsional, nilai tambah) **Pie chart** pakai Chart.js CDN: 1 chart pendapatan + 1 chart pengeluaran. Include `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>`. Kalau rumit, skip dulu — tabel breakdown sudah cukup untuk demo.
- [ ] Edge case: kalau semua 0 → "Belum ada transaksi pada periode ini"

### 6.5 Update Dashboard (`www/admin/dashboard.php`)

- [ ] Tambah 4 stat card di dashboard: Total Kos, Total Penyewa, **Pendapatan Bulan Ini**, **Pengeluaran Bulan Ini**
- [ ] Tambah menu di navbar/sidebar admin: Kos, Penyewa, Kategori, Pendapatan, Pengeluaran, **Laporan**

### 6.6 TEST FAZE 6

- [ ] Daftar kategori tampil 10 seed default + bisa filter tipe
- [ ] Tambah kategori baru "Bonus" (tipe Pendapatan) → muncul di daftar
- [ ] Edit kategori "Sewa" → "Sewa Bulanan" → berubah
- [ ] **Hapus kategori "Sewa" yang dipakai transaksi** → DITOLAK (BR-9), pesan muncul
- [ ] Hapus kategori "Bonus" (tidak dipakai) → berhasil
- [ ] Tambah pendapatan: kos Melati, kategori Sewa, tanggal hari ini, jumlah 500000 → muncul di daftar
- [ ] Filter pendapatan per bulan berfungsi
- [ ] Tambah pengeluaran: kos Melati, kategori Listrik, 150000 → muncul
- [ ] Laporan keuangan bulan ini: stat card pendapatan/pengeluaran/saldo terisi benar
- [ ] Breakdown per kategori tampil dengan total + persentase
- [ ] Ganti bulan di filter → data laporan berubah
- [ ] Bulan tanpa transaksi → tampil pesan empty state

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 7 (khususnya query laporan & BR-9)**

```bash
git add . && git commit -m "feat: modul keuangan kategori+pendapatan+pengeluaran+laporan (Faze 6)" && git push
```

---

## FAZE 7 — POLISHING & STYLING PROFESIONAL (~1.5 jam)

> Tujuan: UI konsisten, profesional, siap screenshot. **Patokan utama: PRD section 8.3.**
> Golden rule: **TIDAK BOLEH ADA EMOJI DECORATIVE di UI.**

### 6.1 Konsistensi layout (semua halaman)

- [ ] Navbar sama di semua halaman (public + admin): brand "Kos Qullah" di kiri (tanpa emoji), menu Home + (Login/Logout) + (Admin menu jika session login)
- [ ] Container utama `<div class="container py-4">` atau `py-5` di semua halaman (padding vertikal konsisten)
- [ ] Footer sama di semua halaman: `<footer class="bg-light py-3 mt-5 text-center text-muted">© 2026 M. Rizqullah - Tugas 2 Cloud Computing</footer>` (tanpa emoji)
- [ ] Bootstrap CDN (CSS + Icons) di `<head>` semua halaman, JS bundle sebelum `</body>`

### 6.2 Komponen UI (sesuai PRD 8.3)

- [ ] **Tabel data** semua pakai `<table class="table table-striped table-hover table-bordered align-middle">` dalam `<div class="table-responsive">`
- [ ] **Card** semua pakai `<div class="card h-100 shadow-sm">` dengan `.card-header` (judul) + `.card-body`
- [ ] **Badge status**: Tersedia/Aktif → `<span class="badge bg-success rounded-pill">`; Penuh/Selesai → `<span class="badge bg-secondary rounded-pill">` (BUKAN merah)
- [ ] **Tombol aksi**: Edit → `btn btn-sm btn-outline-primary`; Hapus → `btn btn-sm btn-outline-danger`; Tambah → `btn btn-primary`
- [ ] **Form**: tiap input pakai `<label class="form-label">` + `<input class="form-control">` + `required` HTML5
- [ ] **Foto thumbnail tabel**: `<img class="rounded" style="width:50px;height:50px;object-fit:cover">`
- [ ] **Foto besar detail**: `<img class="img-fluid rounded shadow-sm" style="max-height:400px">`
- [ ] **Empty state**: kalau 0 record → `<div class="text-center text-muted py-5">Belum ada data.</div>`

### 6.3 Format data (konsisten di semua halaman)

- [ ] Harga: `Rp 500.000` via `number_format($harga, 0, ',', '.')` (BUKAN `500000`)
- [ ] Tanggal: format `10 Agu 2026` via `date('j M Y', strtotime($tgl))` (BUKAN raw timestamp)
- [ ] Alamat di tabel: truncate ~50 char + ellipsis, full text via `title="..."` tooltip

### 6.4 Flash message (sukses/error)

- [ ] Helper function `set_flash($type, $msg)` → simpan di `$_SESSION['flash']`
- [ ] Helper `show_flash()` → render Bootstrap alert + auto-unset
- [ ] Alert sukses: `<div class="alert alert-success alert-dismissible">`
- [ ] Alert error: `<div class="alert alert-danger alert-dismissible">`
- [ ] Pasang `show_flash()` di semua halaman target redirect (dashboard, daftar kos, daftar penyewa)

### 6.5 Validasi & UX

- [ ] Form: HTML5 `required` + tipe input sesuai (`type="number" min="1"` untuk harga/jumlah, `type="email"` untuk email)
- [ ] Konfirmasi hapus: pakai `onclick="return confirm('Yakin hapus kos ini?')"` ATAU Bootstrap modal
- [ ] Konfirmasi hapus pakai ikon Bootstrap (`<i class="bi bi-trash"></i> Hapus`) — BUKAN emoji
- [ ] Tombol edit pakai `<i class="bi bi-pencil"></i> Edit` — BUKAN emoji
- [ ] Tombol lihat detail pakai `<i class="bi bi-eye"></i> Detail` atau text saja

### 6.6 Audit Anti-Pattern (final check sebelum screenshot)

- [ ] **Cari emoji dekoratif** di semua file PHP → hapus semua, ganti Bootstrap Icons atau text. (Grep: `[🏠📷⚠️✏️🗑️🔍]` atau karakter emoji apapun)
- [ ] Tidak ada warna terang default Bootstrap biru (`#0d6efd`) di area besar — sudah di-override oleh `--primary`
- [ ] Tidak ada background gradient / pattern
- [ ] Font pakai system-ui / Inter / Roboto (tidak Comic Sans / decorative)
- [ ] Tabel mobile: cek di width sempit, tidak overflow horizontal (kalau ada, tambah `table-responsive`)
- [ ] Form ada label jelas (bukan placeholder saja)
- [ ] Tidak ada debug code (`var_dump`, `print_r`, `echo "test"` di production view)

### 6.7 End-to-end test (jalankan SEMUA flow)

- [ ] Homepage table view → search → filter → toggle card → klik detail
- [ ] Detail kos → tampil info + JOIN penyewa
- [ ] Login admin → redirect dashboard
- [ ] Dashboard → tambah kos (+ foto) → sukses flash
- [ ] Edit kos → ganti foto → sukses flash
- [ ] Hapus kos kosong → sukses
- [ ] Hapus kos berpenyewa → error flash (BR-4)
- [ ] CRUD penyewa lengkap → semua ada flash message
- [ ] Logout → balik homepage
- [ ] Akses `admin/*.php` tanpa login → redirect ke login
- [ ] Cek di browser berbeda (Chrome + Firefox) untuk pastikan tidak ada quirk

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 7 (khususnya audit emoji + komponen UI)**

**👉 REVIEW DENGAN AI SEBELUM LANJUT FAZE 7**

```bash
git add . && git commit -m "feat: polishing UI Bootstrap (Faze 6)" && git push
```

---

## FAZE 8 — DEPLOY LAB RHA + SCREENSHOT (~2.5 jam)

> Tujuan: Verifikasi environment asli + ambil semua screenshot.

### 8.1 SSH & Deploy

- [ ] SSH ke Lab RHA: `ssh <user>@<host-lab>`
- [ ] Clone repo: `git clone <url-private-repo> uas-qullah && cd uas-qullah`
- [ ] Build & run: `podman compose up -d --build`
- [ ] Tunggu sampai 3 container Up
- [ ] `podman ps` → verifikasi 3 baris dengan suffix `-qullah`

### 8.2 Ambil Screenshot (urut dari PRD section 14)

**SS-01 — Source code & struktur (6 screenshot):**
- [ ] SS-01a: struktur direktori (`tree` / `ls -R`)
- [ ] SS-01b: isi `db_kos.sql` di editor
- [ ] SS-01c: isi `php-apache.Containerfile` (mysqli + nano terlihat)
- [ ] SS-01d: isi `mariadb.Containerfile` (COPY db_kos.sql terlihat)
- [ ] SS-01e: isi `compose.yml` (3 services, port, depends_on, volume)
- [ ] SS-01f: kode PHP (index.php, tambah_kos.php, dll)

**SS-06 — Build & run (1 screenshot):**
- [ ] SS-06: output `podman compose up -d --build` (log Done, Started)

**SS-07 — Verify container (2 screenshot):**
- [ ] SS-07a: `podman ps` (3 baris Up, port 8000/3306/8001, names `-qullah`)
- [ ] SS-07b: `podman ps -a` (semua container terlihat)

**SS-08 — phpMyAdmin verify (3 screenshot):**
- [ ] SS-08a: halaman login phpMyAdmin di `:8001`
- [ ] SS-08b: dashboard setelah login (DB `db_kos` di sidebar)
- [ ] SS-08c: Browse tabel `kos` (2 record terlihat)

**SS-09 — Homepage (3 screenshot):**
- [ ] SS-09a: homepage default (tabel 9 kolom, 2 record)
- [ ] SS-09b: homepage setelah toggle (card view)
- [ ] SS-09c: `detail.php?id=1` (info Melati Jaya + JOIN daftar penyewa)

**SS-10 — CRUD verify (12 screenshot):**
- [ ] SS-10a: halaman login admin
- [ ] SS-10b: form tambah kos (input file foto terlihat)
- [ ] SS-10c: hasil CREATE (record baru muncul)
- [ ] SS-10d: form edit kos (pre-fill)
- [ ] SS-10e: hasil UPDATE (data berubah)
- [ ] SS-10f: dialog confirm DELETE
- [ ] SS-10g: hasil DELETE (record berkurang)
- [ ] SS-10h: BR-4 demo (coba hapus kos berpenyewa → pesan tolak)
- [ ] SS-10i: daftar penyewa (`penyewa.php`, JOIN nama kos terlihat)
- [ ] SS-10j: form tambah penyewa (dropdown kos)
- [ ] SS-10k: form edit penyewa (pre-fill)
- [ ] SS-10l: hasil DELETE penyewa

**SS-10 (lanjut) — Modul Keuangan B5 (7 screenshot):**
- [ ] SS-10m: daftar kategori (badge tipe, 10 seed default terlihat)
- [ ] SS-10n: form tambah kategori (nama + radio tipe)
- [ ] SS-10o: BR-9 demo (coba hapus kategori "Sewa" yang dipakai → pesan tolak)
- [ ] SS-10p: daftar pendapatan (JOIN nama kos + kategori, filter bulan)
- [ ] SS-10q: form tambah pendapatan (dropdown kos + dropdown kategori filter Pendapatan)
- [ ] SS-10r: form tambah pengeluaran (kategori filter Pengeluaran)
- [ ] SS-10s: laporan keuangan bulanan (3 stat card + breakdown per kategori + opsional chart)

**SS-11 — Stop container (2 screenshot):**
- [ ] SS-11a: output `podman compose down` (log stopping/removing)
- [ ] SS-11b: `podman ps -a` (STATUS Exited)

**TOTAL SCREENSHOT: 36**

### 8.3 Final stop (Soal 11)

- [ ] `podman compose down`
- [ ] `podman ps -a` → semua Exited (SS-11b)

---

## 🎯 COMPLETION CRITERIA (Sebelum Submit)

- [ ] Semua 11 soal terpenuhi (cek PRD section 9.3)
- [ ] 29 screenshot terkumpul
- [ ] Code ter-push ke Git (semua faze committed)
- [ ] Repo private berisi semua deliverables (PRD section 12)
- [ ] Final test di Lab RHA jalan tanpa error
- [ ] Demo CRUD lengkap (kos + penyewa) berhasil
- [ ] Homepage tampil seluruh data tabel (Soal 9 ✅)

---

## 🆘 KAPAN PANGGIL AI UNTUK HELP

| Situasi | Contoh Pesan ke AI |
|---------|-------------------|
| **Review akhir faze** | "Faze 2 selesai. Ini index.php saya: [paste]. Review sebelum lanjut?" |
| **Stuck/error** | "Homepage blank putih. Error log: [paste]. Kode: [paste]. Fix?" |
| **Tanya konsep** | "Gimana cara kerja toggle JS card/table yang efisien?" |
| **Verifikasi design** | "Apakah SQL saya sudah benar? [paste db_kos.sql]" |
| **Tanya query kompleks** | "Query breakdown laporan saya salah, ini SQL: [paste]. Fix?" |
| **Debug deploy** | "Di Lab RHA podman compose gagal build: [paste error]" |

---

## ⚠️ TOP 15 ERROR UMUM (antisipasi!)

| # | Error | Fix |
|---|-------|-----|
| 1 | "Connection refused" PHP→DB | Host pakai `mariadb-qullah`, bukan `localhost` |
| 2 | phpMyAdmin login gagal | Pakai `kos_user`/`kos_pass123`, bukan root |
| 3 | Upload foto gagal ("Gagal menyimpan foto") | `chmod 777 www/uploads` di host (file clone dimiliki uid user, sedangkan Apache container jalan sebagai www-data uid 33 yang butuh izin tulis) |
| 4 | Session tidak persist | `session_start()` di paling atas tiap file butuh session |
| 5 | bcrypt hash tidak match | Generate via `php -r "echo password_hash(...);"` |
| 6 | Port dipakai app lain | `ss -tlnp \| grep <port>`, ganti port di compose |
| 7 | SQL tidak auto-import | Cek `COPY db_kos.sql /docker-entrypoint-initdb.d/` di Containerfile |
| 8 | Volume DB cache data lama | `podman compose down -v` untuk reset volume |
| 9 | Hapus kos gagal padahal kosong | Echo query pre-check, debug manual |
| 10 | Foto tidak tampil di UI | Path relatif `uploads/xxx.jpg` (TANPA `/` di awal) |
| 11 | **Saldo laporan minus padahal ada transaksi** | Cek filter `MONTH()`/`YEAR()` di query — pakai placeholder `?` jangan concat string |
| 12 | **Dropdown kategori di form transaksi kosong** | Cek `WHERE tipe='Pendapatan'` (atau Pengeluaran) — pastikan filter tipe benar |
| 13 | **Hapus kategori gagal padahal tidak dipakai** | BR-9 pre-check 2 tabel (pendapatan + pengeluaran), echo kedua COUNT untuk debug |
| 14 | **Breakdown laporan NULL/0** | Pakai `LEFT JOIN` + `COALESCE(SUM(x),0)` agar kategori tanpa transaksi tetap tampil |
| 15 | **Chart.js tidak render** | Pastikan `<canvas id="...">` + script init di bawah canvas, cek console browser untuk error JS |

---

## 📅 ESTIMASI TIMELINE

| Faze | Estimasi | Kumulatif |
|------|----------|-----------|
| 0. Setup | 30 menit | 0.5 jam |
| 1. SQL + Containerfile | 1.5 jam | 2 jam |
| 2. Homepage | 1 jam | 3 jam |
| 3. Auth | 1 jam | 4 jam |
| 4. CRUD kos | 2 jam | 6 jam |
| 5. Detail + Penyewa | 1.5 jam | 7.5 jam |
| 6. **Keuangan (B5)** | **3 jam** | **10.5 jam** |
| 7. Polishing | 1.5 jam | 12 jam |
| 8. Deploy + SS | 2.5 jam | **14.5 jam** |

**Rekomendasi jadwal (spread 4 hari):**
- **Hari 1:** Faze 0-2 (foundation + homepage jalan, ~4.5 jam)
- **Hari 2:** Faze 3-5 (auth + CRUD kos + penyewa, ~4.5 jam)
- **Hari 3:** Faze 6 (modul keuangan, ~3 jam) — fokus penuh karena paling kompleks
- **Hari 4:** Faze 7-8 (polish + deploy + screenshot, ~4 jam)

---

## 📝 CATATAN PROGRESS PRIBADI

> Tulis progress / blocker / hal penting di sini saat coding.

**Hari 1 — Tanggal: _____**
-

**Hari 2 — Tanggal: _____**
-

**Hari 3 — Tanggal: _____**
-

**Hari 4 — Tanggal: _____**
-

**Blocker / hal yang perlu diingat:**
-

---

> File ini adalah working document. Update tiap selesai faze. Happy coding! 🚀
