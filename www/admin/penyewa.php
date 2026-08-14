<?php
// admin/penyewa.php - daftar penyewa (JOIN nama kos)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$hasil = mysqli_query($koneksi, "SELECT p.*, k.nama_kos FROM penyewa p JOIN kos k ON p.kos_id = k.id ORDER BY p.status_sewa DESC, p.tgl_masuk DESC");
$rows  = mysqli_fetch_all($hasil, MYSQLI_ASSOC);

render_header('Daftar Penyewa', 'penyewa');
show_flash();
?>
<div class="page-header">
    <h4 class="mb-0"><i class="bi bi-people"></i> Daftar Penyewa</h4>
    <a href="tambah_penyewa.php" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Tambah Penyewa</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>ID</th><th>Nama</th><th>No HP</th><th>Email</th><th>Kos</th><th>Tgl Masuk</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada penyewa. <a href="tambah_penyewa.php">Tambah sekarang</a>.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td class="fw-semibold"><?= e($r['nama']) ?></td>
                <td><?= e($r['no_hp']) ?></td>
                <td><?= e($r['email'] ?: '-') ?></td>
                <td><a href="/detail.php?id=<?= (int)$r['kos_id'] ?>" class="text-decoration-none"><?= e($r['nama_kos']) ?></a></td>
                <td><?= tgl($r['tgl_masuk']) ?></td>
                <td>
                    <?php if ($r['status_sewa']==='Aktif'): ?>
                        <span class="badge bg-success rounded-pill">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill">Selesai</span>
                    <?php endif; ?>
                </td>
                <td class="text-center text-nowrap">
                    <a href="edit_penyewa.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="hapus_penyewa.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus penyewa <?= e($r['nama']) ?>?')"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php render_footer(); ?>
