<?php
// admin/edit_kategori.php - UPDATE kategori
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM kategori WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$k) { set_flash('error', 'Kategori tidak ditemukan.'); header('Location: kategori.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $k['nama'] = trim($_POST['nama'] ?? '');
    $k['tipe'] = $_POST['tipe'] ?? $k['tipe'];
    if ($k['nama'] === '') $errors[] = 'Nama kategori wajib diisi.';
    if (!in_array($k['tipe'], ['Pendapatan','Pengeluaran'])) $errors[] = 'Tipe tidak valid.';
    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE kategori SET nama=?, tipe=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'ssi', $k['nama'], $k['tipe'], $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Kategori diperbarui.'); header('Location: kategori.php'); exit; }
        $errors[] = 'Gagal update.';
    }
}

render_header('Edit Kategori', 'kategori');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Kategori</h4></div>
<a href="kategori.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="row g-3" novalidate>
    <div class="col-md-7">
        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
        <input class="form-control" name="nama" value="<?= e($k['nama']) ?>" required>
    </div>
    <div class="col-md-5">
        <label class="form-label d-block">Tipe</label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="tipe" id="t1" value="Pendapatan" <?= $k['tipe']==='Pendapatan'?'checked':'' ?>>
            <label class="btn btn-outline-success" for="t1">Pendapatan</label>
            <input type="radio" class="btn-check" name="tipe" id="t2" value="Pengeluaran" <?= $k['tipe']==='Pengeluaran'?'checked':'' ?>>
            <label class="btn btn-outline-danger" for="t2">Pengeluaran</label>
        </div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
        <a href="kategori.php" class="btn btn-link text-decoration-none">Batal</a>
    </div>
</form></div></div>
<?php render_footer(); ?>
