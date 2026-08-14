<?php
// admin/tambah_kos.php - CREATE kos + upload foto (Soal 10, B3)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$errors = [];
$old = ['nama_kos'=>'','alamat'=>'','tipe_kamar'=>'Putri','harga_per_bulan'=>'','jumlah_kamar'=>'','status'=>'Tersedia'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama_kos'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tipe   = $_POST['tipe_kamar'] ?? '';
    $harga  = (int)($_POST['harga_per_bulan'] ?? 0);
    $jumlah = (int)($_POST['jumlah_kamar'] ?? 0);
    $status = $_POST['status'] ?? 'Tersedia';
    $old = ['nama_kos'=>$nama,'alamat'=>$alamat,'tipe_kamar'=>$tipe,'harga_per_bulan'=>$harga,'jumlah_kamar'=>$jumlah,'status'=>$status];

    if ($nama === '')   $errors[] = 'Nama kos wajib diisi.';
    if ($alamat === '') $errors[] = 'Alamat wajib diisi.';
    if (!in_array($tipe, ['Putra','Putri','Campur'])) $errors[] = 'Tipe tidak valid.';
    if (!in_array($status, ['Tersedia','Penuh'])) $errors[] = 'Status tidak valid.';
    if ($harga  <= 0) $errors[] = 'Harga harus > 0.';
    if ($jumlah <= 0) $errors[] = 'Jumlah kamar harus > 0.';

    // Upload foto opsional (BR-3: max 2MB, JPG/PNG/WebP)
    $foto = null;
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $max  = 2 * 1024 * 1024;
        $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $boleh = ['jpg','jpeg','png','webp'];
        if ($_FILES['foto']['size'] > $max) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } elseif (!in_array($ext, $boleh)) {
            $errors[] = 'Format foto harus JPG/PNG/WebP.';
        } elseif ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal upload foto (error code ' . $_FILES['foto']['error'] . ').';
        } else {
            $foto = 'uploads/kos_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!is_dir(__DIR__ . '/../uploads')) mkdir(__DIR__ . '/../uploads', 0775, true);
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../' . $foto)) {
                $errors[] = 'Gagal menyimpan foto.'; $foto = null;
            }
        }
    }

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO kos (nama_kos, alamat, tipe_kamar, harga_per_bulan, jumlah_kamar, status, foto) VALUES (?,?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'sssiiss', $nama, $alamat, $tipe, $harga, $jumlah, $status, $foto);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) {
            set_flash('success', 'Kos "' . $nama . '" berhasil ditambahkan.');
            header('Location: dashboard.php'); exit;
        }
        $errors[] = 'Gagal menyimpan ke database.';
    }
}

render_header('Tambah Kos', 'kos');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-plus-lg"></i> Tambah Kos Baru</h4></div>
<a href="dashboard.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" novalidate>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Nama Kos <span class="text-danger">*</span></label>
            <input class="form-control" name="nama_kos" value="<?= e($old['nama_kos']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipe Kamar <span class="text-danger">*</span></label>
            <select class="form-select" name="tipe_kamar">
                <?php foreach (['Putra','Putri','Campur'] as $t): ?>
                    <option <?= $old['tipe_kamar']===$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Alamat <span class="text-danger">*</span></label>
            <textarea class="form-control" name="alamat" rows="2" required><?= e($old['alamat']) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Harga / bulan (Rp) <span class="text-danger">*</span></label>
            <input type="number" min="1" step="1000" class="form-control" name="harga_per_bulan" value="<?= e($old['harga_per_bulan']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah Kamar <span class="text-danger">*</span></label>
            <input type="number" min="1" class="form-control" name="jumlah_kamar" value="<?= e($old['jumlah_kamar']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="Tersedia" <?= $old['status']==='Tersedia'?'selected':'' ?>>Tersedia</option>
                <option value="Penuh"    <?= $old['status']==='Penuh'?'selected':'' ?>>Penuh</option>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Foto Kos (opsional, maks 2MB, JPG/PNG/WebP)</label>
            <input type="file" class="form-control" name="foto" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Kosongkan untuk memakai foto default.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Kos</button>
            <a href="dashboard.php" class="btn btn-link text-decoration-none">Batal</a>
        </div>
    </div>
</form></div></div>
<?php render_footer(); ?>
