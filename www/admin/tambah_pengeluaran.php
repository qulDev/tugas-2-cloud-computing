<?php
// admin/tambah_pengeluaran.php - CREATE pengeluaran (kategori filter Pengeluaran, BR-8)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$errors = [];
$old = ['kos_id'=>'','kategori_id'=>'','tanggal'=>date('Y-m-d'),'jumlah'=>'','keterangan'=>''];

$koss = mysqli_fetch_all(mysqli_query($koneksi, 'SELECT id, nama_kos FROM kos ORDER BY nama_kos'), MYSQLI_ASSOC);
$kats = mysqli_fetch_all(mysqli_query($koneksi, "SELECT id, nama FROM kategori WHERE tipe='Pengeluaran' ORDER BY nama"), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['kos_id']      = (int)($_POST['kos_id'] ?? 0);
    $old['kategori_id'] = (int)($_POST['kategori_id'] ?? 0);
    $old['tanggal']     = $_POST['tanggal'] ?? date('Y-m-d');
    $old['jumlah']      = (int)($_POST['jumlah'] ?? 0);
    $old['keterangan']  = trim($_POST['keterangan'] ?? '');

    if ($old['kos_id'] <= 0)      $errors[] = 'Pilih kos.';
    if ($old['kategori_id'] <= 0) $errors[] = 'Pilih kategori.';
    if ($old['jumlah'] <= 0)      $errors[] = 'Jumlah harus > 0.';

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO pengeluaran (kos_id, kategori_id, tanggal, jumlah, keterangan) VALUES (?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'iisis', $old['kos_id'], $old['kategori_id'], $old['tanggal'], $old['jumlah'], $old['keterangan']);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Pengeluaran ditambahkan.'); header('Location: pengeluaran.php'); exit; }
        $errors[] = 'Gagal menyimpan.';
    }
}

render_header('Tambah Pengeluaran', 'pengeluaran');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-plus-lg"></i> Tambah Pengeluaran</h4></div>
<a href="pengeluaran.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php if (!$koss): ?><div class="alert alert-warning">Belum ada kos. <a href="tambah_kos.php">Tambah kos dulu</a>.</div><?php endif; ?>
<?php if (!$kats): ?><div class="alert alert-warning">Belum ada kategori Pengeluaran. <a href="tambah_kategori.php">Tambah kategori dulu</a>.</div><?php endif; ?>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Kos <span class="text-danger">*</span></label>
            <select class="form-select" name="kos_id" required>
                <option value="">-- pilih kos --</option>
                <?php foreach ($koss as $k): ?><option value="<?= (int)$k['id'] ?>" <?= $old['kos_id']===(int)$k['id']?'selected':'' ?>><?= e($k['nama_kos']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Kategori <span class="text-danger">*</span></label>
            <select class="form-select" name="kategori_id" required>
                <option value="">-- pilih kategori --</option>
                <?php foreach ($kats as $k): ?><option value="<?= (int)$k['id'] ?>" <?= $old['kategori_id']===(int)$k['id']?'selected':'' ?>><?= e($k['nama']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal</label>
            <input type="date" class="form-control" name="tanggal" value="<?= e($old['tanggal']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Jumlah (Rp) <span class="text-danger">*</span></label>
            <input type="number" min="1" step="500" class="form-control" name="jumlah" value="<?= e($old['jumlah']) ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">Keterangan</label>
            <textarea class="form-control" name="keterangan" rows="2"><?= e($old['keterangan']) ?></textarea>
        </div>
        <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="pengeluaran.php" class="btn btn-link text-decoration-none">Batal</a>
        </div>
    </div>
</form></div></div>
<?php render_footer(); ?>
