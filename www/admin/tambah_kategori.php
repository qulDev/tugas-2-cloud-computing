<?php
// admin/tambah_kategori.php - CREATE kategori (radio tipe)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$errors = [];
$nama = ''; $tipe = 'Pendapatan';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tipe = $_POST['tipe'] ?? '';
    if ($nama === '') $errors[] = 'Nama kategori wajib diisi.';
    if (!in_array($tipe, ['Pendapatan','Pengeluaran'])) $errors[] = 'Tipe tidak valid.';
    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO kategori (nama, tipe) VALUES (?, ?)');
        mysqli_stmt_bind_param($stmt, 'ss', $nama, $tipe);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Kategori ditambahkan.'); header('Location: kategori.php'); exit; }
        $errors[] = 'Gagal menyimpan.';
    }
}

render_header('Tambah Kategori', 'kategori');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-plus-lg"></i> Tambah Kategori</h4></div>
<a href="kategori.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" class="row g-3" novalidate>
    <div class="col-md-7">
        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
        <input class="form-control" name="nama" value="<?= e($nama) ?>" required autofocus>
    </div>
    <div class="col-md-5">
        <label class="form-label d-block">Tipe <span class="text-danger">*</span></label>
        <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="tipe" id="t1" value="Pendapatan" <?= $tipe==='Pendapatan'?'checked':'' ?>>
            <label class="btn btn-outline-success" for="t1">Pendapatan</label>
            <input type="radio" class="btn-check" name="tipe" id="t2" value="Pengeluaran" <?= $tipe==='Pengeluaran'?'checked':'' ?>>
            <label class="btn btn-outline-danger" for="t2">Pengeluaran</label>
        </div>
    </div>
    <div class="col-12">
        <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
        <a href="kategori.php" class="btn btn-link text-decoration-none">Batal</a>
    </div>
</form></div></div>
<?php render_footer(); ?>
