<?php
// admin/edit_pengeluaran.php - UPDATE pengeluaran
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM pengeluaran WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$r) { set_flash('error', 'Transaksi tidak ditemukan.'); header('Location: pengeluaran.php'); exit; }

$errors = [];
$koss = mysqli_fetch_all(mysqli_query($koneksi, 'SELECT id, nama_kos FROM kos ORDER BY nama_kos'), MYSQLI_ASSOC);
$kats = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM kategori WHERE tipe='Pengeluaran' ORDER BY nama"), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r['kos_id']      = (int)($_POST['kos_id'] ?? 0);
    $r['kategori_id'] = (int)($_POST['kategori_id'] ?? 0);
    $r['tanggal']     = $_POST['tanggal'] ?? date('Y-m-d');
    $r['jumlah']      = (int)($_POST['jumlah'] ?? 0);
    $r['keterangan']  = trim($_POST['keterangan'] ?? '');
    if ($r['kos_id'] <= 0)      $errors[] = 'Pilih kos.';
    if ($r['kategori_id'] <= 0) $errors[] = 'Pilih kategori.';
    if ($r['jumlah'] <= 0)      $errors[] = 'Jumlah harus > 0.';
    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE pengeluaran SET kos_id=?, kategori_id=?, tanggal=?, jumlah=?, keterangan=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'iisisi', $r['kos_id'], $r['kategori_id'], $r['tanggal'], $r['jumlah'], $r['keterangan'], $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Pengeluaran diperbarui.'); header('Location: pengeluaran.php'); exit; }
        $errors[] = 'Gagal update.';
    }
}

render_header('Edit Pengeluaran', 'pengeluaran');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Pengeluaran</h4></div>
<a href="pengeluaran.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Kos</label>
            <select class="form-select" name="kos_id" required>
                <?php foreach ($koss as $k): ?><option value="<?= (int)$k['id'] ?>" <?= (int)$r['kos_id']===(int)$k['id']?'selected':'' ?>><?= e($k['nama_kos']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kategori</label>
            <select class="form-select" name="kategori_id" required>
                <?php foreach ($kats as $k): ?><option value="<?= (int)$k['id'] ?>" <?= (int)$r['kategori_id']===(int)$k['id']?'selected':'' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" value="<?= e($r['tanggal']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah (Rp)</label>
            <input type="number" min="1" step="500" class="form-control" name="jumlah" value="<?= (int)$r['jumlah'] ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="2"><?= e($r['keterangan']) ?></textarea>
        </div>
        <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
            <a href="pengeluaran.php" class="btn btn-link text-decoration-none">Batal</a>
        </div>
    </div>
</form></div></div>
<?php render_footer(); ?>
