<?php
// detail.php - Detail kos + JOIN daftar penyewa (PUBLIC, B3 + nilai tambah relasi)
require_once __DIR__ . '/partials.php';
require_once __DIR__ . '/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: /index.php'); exit; }

$stmt = mysqli_prepare($koneksi, 'SELECT * FROM kos WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$kos = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$kos) {
    render_header('Kos Tidak Ditemukan');
    echo '<div class="alert alert-warning">Kos tidak ditemukan.</div>';
    echo '<a href="/index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>';
    render_footer();
    exit;
}

// JOIN: daftar penyewa kos ini (nilai tambah FK 1:N)
$stmt = mysqli_prepare($koneksi, 'SELECT * FROM penyewa WHERE kos_id = ? ORDER BY tgl_masuk DESC');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$penyewa = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

render_header(e($kos['nama_kos']));
$foto = $kos['foto'] ?: 'uploads/default.jpg';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="/index.php" class="btn btn-link text-decoration-none ps-0"><i class="bi bi-arrow-left"></i> Kembali ke daftar</a>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <img src="/<?= e($foto) ?>" class="img-fluid rounded shadow-sm w-100" style="max-height:380px;object-fit:cover" alt="foto kos">
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <h3 class="card-title mb-1"><?= e($kos['nama_kos']) ?></h3>
                    <?php if ($kos['status']==='Tersedia'): ?>
                        <span class="badge bg-success rounded-pill">Tersedia</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill">Penuh</span>
                    <?php endif; ?>
                </div>
                <p class="text-muted mb-3"><i class="bi bi-geo-alt"></i> <?= e($kos['alamat']) ?></p>
                <table class="table table-borderless mb-0">
                    <tr><th class="text-muted w-25">Tipe Kamar</th><td><?= e($kos['tipe_kamar']) ?></td></tr>
                    <tr><th class="text-muted">Harga / bulan</th><td class="fw-bold text-primary"><?= rp((int)$kos['harga_per_bulan']) ?></td></tr>
                    <tr><th class="text-muted">Jumlah Kamar</th><td><?= (int)$kos['jumlah_kamar'] ?></td></tr>
                    <tr><th class="text-muted">Status</th><td><?= e($kos['status']) ?></td></tr>
                    <tr><th class="text-muted">Terdaftar</th><td class="text-muted"><?= tgl($kos['created_at']) ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Daftar penyewa (JOIN FK) -->
<div class="page-header mt-5">
    <h5 class="mb-0"><i class="bi bi-people"></i> Penyewa di Kos Ini</h5>
</div>
<?php if (!$penyewa): ?>
    <div class="text-center text-muted py-4"><i class="bi bi-inbox fs-2 d-block mb-1"></i> Belum ada penyewa di kos ini.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Nama</th><th>No HP</th><th>Email</th><th>Tgl Masuk</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($penyewa as $p): ?>
                <tr>
                    <td class="fw-semibold"><?= e($p['nama']) ?></td>
                    <td><?= e($p['no_hp']) ?></td>
                    <td><?= e($p['email'] ?: '-') ?></td>
                    <td><?= tgl($p['tgl_masuk']) ?></td>
                    <td>
                        <?php if ($p['status_sewa']==='Aktif'): ?>
                            <span class="badge bg-success rounded-pill">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill">Selesai</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
<?php endif; ?>
<?php render_footer(); ?>
