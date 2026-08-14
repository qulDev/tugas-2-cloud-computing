<?php
// admin/tambah_penyewa.php - CREATE penyewa (dropdown kos FK)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$old = ['nama'=>'','no_hp'=>'','email'=>'','kos_id'=>'','tgl_masuk'=>date('Y-m-d'),'status_sewa'=>'Aktif'];
$errors = [];

$koss = mysqli_fetch_all(mysqli_query($koneksi, 'SELECT id, nama_kos FROM kos ORDER BY nama_kos'), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $kos_id = (int)($_POST['kos_id'] ?? 0);
    $tgl    = $_POST['tgl_masuk'] ?? date('Y-m-d');
    $status = $_POST['status_sewa'] ?? 'Aktif';
    $old = ['nama'=>$nama,'no_hp'=>$no_hp,'email'=>$email,'kos_id'=>$kos_id,'tgl_masuk'=>$tgl,'status_sewa'=>$status];

    if ($nama === '')  $errors[] = 'Nama wajib diisi.';
    if ($no_hp === '') $errors[] = 'No HP wajib diisi.';
    if ($kos_id <= 0)  $errors[] = 'Pilih kos.';
    if (!in_array($status, ['Aktif','Selesai'])) $errors[] = 'Status tidak valid.';

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi, 'INSERT INTO penyewa (nama, no_hp, email, kos_id, tgl_masuk, status_sewa) VALUES (?,?,?,?,?,?)');
        mysqli_stmt_bind_param($stmt, 'sssiss', $nama, $no_hp, $email, $kos_id, $tgl, $status);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if ($ok) { set_flash('success', 'Penyewa ditambahkan.'); header('Location: penyewa.php'); exit; }
        $errors[] = 'Gagal menyimpan ke database.';
    }
}

render_header('Tambah Penyewa', 'penyewa');
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-plus-lg"></i> Tambah Penyewa</h4></div>
<a href="penyewa.php" class="btn btn-link text-decoration-none ps-0 mb-3"><i class="bi bi-arrow-left"></i> Kembali</a>

<div class="card shadow-sm"><div class="card-body">
<?php if (!$koss): ?><div class="alert alert-warning">Belum ada kos. <a href="tambah_kos.php">Tambah kos dulu</a>.</div><?php endif; ?>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2 mb-2"><?= e($er) ?></div><?php endforeach; ?>
<form method="post" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nama <span class="text-danger">*</span></label>
            <input class="form-control" name="nama" value="<?= e($old['nama']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">No HP <span class="text-danger">*</span></label>
            <input class="form-control" name="no_hp" value="<?= e($old['no_hp']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" name="email" value="<?= e($old['email']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Kos <span class="text-danger">*</span></label>
            <select class="form-select" name="kos_id" required>
                <option value="">-- pilih kos --</option>
                <?php foreach ($koss as $k): ?>
                    <option value="<?= (int)$k['id'] ?>" <?= (int)$old['kos_id']===(int)$k['id']?'selected':'' ?>><?= e($k['nama_kos']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tanggal Masuk</label>
            <input type="date" class="form-control" name="tgl_masuk" value="<?= e($old['tgl_masuk']) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status Sewa</label>
            <select class="form-select" name="status_sewa">
                <option value="Aktif"  <?= $old['status_sewa']==='Aktif'?'selected':'' ?>>Aktif</option>
                <option value="Selesai" <?= $old['status_sewa']==='Selesai'?'selected':'' ?>>Selesai</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="penyewa.php" class="btn btn-link text-decoration-none">Batal</a>
        </div>
    </div>
</form></div></div>
<?php render_footer(); ?>
