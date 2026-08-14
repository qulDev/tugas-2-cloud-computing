<?php
// admin/kategori.php - daftar kategori keuangan (filter tipe, B5)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$tipe = $_GET['tipe'] ?? '';
if (!in_array($tipe, ['','Pendapatan','Pengeluaran'])) $tipe = '';
if ($tipe) {
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM kategori WHERE tipe = ? ORDER BY tipe, nama');
    mysqli_stmt_bind_param($stmt, 's', $tipe);
} else {
    $stmt = mysqli_prepare($koneksi, 'SELECT * FROM kategori ORDER BY tipe, nama');
}
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

render_header('Kategori Keuangan', 'kategori');
show_flash();
?>
<div class="page-header">
    <h4 class="mb-0"><i class="bi bi-tags"></i> Kategori Keuangan</h4>
    <a href="tambah_kategori.php" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Tambah Kategori</a>
</div>

<div class="btn-group btn-group-sm mb-3" role="group">
    <a href="kategori.php"          class="btn btn-outline-primary <?= $tipe===''?'active':'' ?>">Semua</a>
    <a href="kategori.php?tipe=Pendapatan"  class="btn btn-outline-success <?= $tipe==='Pendapatan'?'active':'' ?>">Pendapatan</a>
    <a href="kategori.php?tipe=Pengeluaran" class="btn btn-outline-danger <?= $tipe==='Pengeluaran'?'active':'' ?>">Pengeluaran</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>ID</th><th>Nama Kategori</th><th>Tipe</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td class="fw-semibold"><?= e($r['nama']) ?></td>
                <td>
                    <?php if ($r['tipe']==='Pendapatan'): ?>
                        <span class="badge bg-success">Pendapatan</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Pengeluaran</span>
                    <?php endif; ?>
                </td>
                <td class="text-center text-nowrap">
                    <a href="edit_kategori.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="hapus_kategori.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori <?= e($r['nama']) ?>?')"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php render_footer(); ?>
