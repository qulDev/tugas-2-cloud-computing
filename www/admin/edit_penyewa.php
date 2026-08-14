<?php
// admin/edit_penyewa.php - UPDATE penyewa
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM penyewa WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$p) { set_flash('error', 'Penyewa tidak ditemukan.'); header('Location: penyewa.php'); exit; }

$errors = [];
$koss = mysqli_fetch_all(mysqli_query($koneksi, 'SELECT id, nama_kos FROM kos ORDER BY nama_kos'), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p['nama']        = trim($_POST['nama'] ?? '');
    $p['no_hp']       = trim($_POST['no_hp'] ?? '');
    $p['email']       = trim($_POST['email'] ?? '');
    $p['kos_id']      = (int)($_POST['kos_id'] ?? 0);
    $p['tgl_masuk']   = $_POST['tgl_masuk'] ?? date('Y-m-d');
    $p['status_sewa'] = $_POST['status_sewa'] ?? 'Aktif';

    if ($p['nama'] === '')  $errors[] = 'Nama wajib diisi.';
    if ($p['no_hp'] === '') $errors[] = 'No HP wajib diisi.';
    if ($p['kos_id'] <= 0)  $errors[] = 'Pilih kos.';
    if (!in_array($p['status_sewa'], ['Aktif','Selesai'])) $errors[] = 'Status tidak valid.';

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'UPDATE penyewa SET nama=?, no_hp=?, email=?, kos_id=?, tgl_masuk=?, status_sewa=? WHERE id=?');
        mysqli_stmt_bind_param($stmt, 'sssissi', $p['nama'], $p['no_hp'], $p['email'], $p['kos_id'], $p['tgl_masuk'], $p['status_sewa'], $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Penyewa diperbarui.'); header('Location: penyewa.php'); exit; }
        $errors[] = 'Gagal update database.';
    }
}

render_header('Edit Penyewa', 'penyewa');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Penyewa</h4></div>
<a href="penyewa.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input class="form-control" name="nama" value="<?= e($p['nama']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">No HP <span class="text-danger">*</span></label>
            <input class="form-control" name="no_hp" value="<?= e($p['no_hp']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= e($p['email']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Kos <span class="text-danger">*</span></label>
            <select class="form-select" name="kos_id" required>
                <?php foreach ($koss as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= (int)$p['kos_id']===(int)$k['id']?'selected':'' ?>><?= e($k['nama_kos']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Masuk</label>
            <input type="date" class="form-control" name="tgl_masuk" value="<?= e($p['tgl_masuk']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status Sewa</label>
            <select class="form-select" name="status_sewa">
                <option value="Aktif"  <?= $p['status_sewa']==='Aktif'?'selected':'' ?>>Aktif</option>
                <option value="Selesai" <?= $p['status_sewa']==='Selesai'?'selected':'' ?>>Selesai</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
            <a href="penyewa.php" class="btn btn-link text-decoration-none">Batal</a>
        </div>
    </div>
</form></div></div>
<?php render_footer(); ?>
