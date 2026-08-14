<?php
// admin/edit_kos.php - UPDATE kos (opsional ganti foto, hapus foto lama)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM kos WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$kos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$kos) { set_flash('error', 'Kos tidak ditemukan.'); header('Location: dashboard.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama_kos'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $tipe   = $_POST['tipe_kamar'] ?? '';
    $harga  = (int)($_POST['harga_per_bulan'] ?? 0);
    $jumlah = (int)($_POST['jumlah_kamar'] ?? 0);
    $status = $_POST['status'] ?? 'Tersedia';
    $kos['nama_kos']=$nama; $kos['alamat']=$alamat; $kos['tipe_kamar']=$tipe;
    $kos['harga_per_bulan']=$harga; $kos['jumlah_kamar']=$jumlah; $kos['status']=$status;

    if ($nama === '')   $errors[] = 'Nama kos wajib diisi.';
    if ($alamat === '') $errors[] = 'Alamat wajib diisi.';
    if (!in_array($tipe, ['Putra','Putri','Campur'])) $errors[] = 'Tipe tidak valid.';
    if (!in_array($status, ['Tersedia','Penuh'])) $errors[] = 'Status tidak valid.';
    if ($harga  <= 0) $errors[] = 'Harga harus > 0.';
    if ($jumlah <= 0) $errors[] = 'Jumlah kamar harus > 0.';

    // Foto opsional
    $foto = $kos['foto'];
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $max  = 2 * 1024 * 1024;
        $ext  = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if ($_FILES['foto']['size'] > $max) {
            $errors[] = 'Ukuran foto maksimal 2MB.';
        } elseif (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $errors[] = 'Format foto harus JPG/PNG/WebP.';
        } else {
            $newFoto = 'uploads/kos_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/../' . $newFoto)) {
                // hapus foto lama kalau bukan default
                if ($kos['foto'] && $kos['foto'] !== 'uploads/default.jpg' && is_file(__DIR__ . '/../' . $kos['foto'])) {
                    unlink(__DIR__ . '/../' . $kos['foto']);
                }
                $foto = $newFoto;
            } else { $errors[] = 'Gagal menyimpan foto baru.'; }
        }
    }

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE kos SET nama_kos=?, alamat=?, tipe_kamar=?, harga_per_bulan=?, jumlah_kamar=?, status=?, foto=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssiissi', $nama, $alamat, $tipe, $harga, $jumlah, $status, $foto, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Kos diperbarui.'); header('Location: dashboard.php'); exit; }
        $errors[] = 'Gagal update database.';
    }
}

render_header('Edit Kos', 'kos');
$foto = $kos['foto'] ?: 'uploads/default.jpg';
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Kos</h4></div>
<a href="dashboard.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" enctype="multipart/form-data" novalidate>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Nama Kos <span class="text-danger">*</span></label>
            <input class="form-control" name="nama_kos" value="<?= e($kos['nama_kos']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tipe Kamar</label>
            <select class="form-select" name="tipe_kamar">
                <?php foreach (['Putra','Putri','Campur'] as $t): ?>
                    <option <?= $kos['tipe_kamar']===$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label">Alamat</label>
            <textarea class="form-control" name="alamat" rows="2" required><?= e($kos['alamat']) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Harga / bulan (Rp)</label>
            <input type="number" min="1" step="1000" class="form-control" name="harga_per_bulan" value="<?= (int)$kos['harga_per_bulan'] ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah Kamar</label>
            <input type="number" min="1" class="form-control" name="jumlah_kamar" value="<?= (int)$kos['jumlah_kamar'] ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="Tersedia" <?= $kos['status']==='Tersedia'?'selected':'' ?>>Tersedia</option>
                <option value="Penuh"    <?= $kos['status']==='Penuh'?'selected':'' ?>>Penuh</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Foto Saat Ini</label><br>
            <img src="/<?= e($foto) ?>" class="rounded" style="width:120px;height:90px;object-fit:cover">
        </div>
        <div class="col-md-6">
            <label class="form-label">Ganti Foto (opsional)</label>
            <input type="file" class="form-control" name="foto" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">Kosongkan jika tidak ingin mengganti foto.</div>
        </div>
        <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
            <a href="dashboard.php" class="btn btn-link text-decoration-none">Batal</a>
        </div>
    </div>
</form></div></div>
<?php render_footer(); ?>
