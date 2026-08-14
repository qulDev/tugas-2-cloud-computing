<?php
// admin/dashboard.php - ringkasan statistik + menu manajemen
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

function scalar($koneksi, string $sql, array $params = [], string $types = '') {
    $stmt = mysqli_prepare($koneksi, $sql);
    if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $v = (int)mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];
    mysqli_stmt_close($stmt);
    return $v;
}

$totalKos      = scalar($koneksi, 'SELECT COUNT(*) FROM kos');
$totalTersedia = scalar($koneksi, "SELECT COUNT(*) FROM kos WHERE status='Tersedia'");
$totalPenyewa  = scalar($koneksi, "SELECT COUNT(*) FROM penyewa WHERE status_sewa='Aktif'");
$bln = (int)date('n'); $thn = (int)date('Y');
$pendBlIni = scalar($koneksi, 'SELECT COALESCE(SUM(jumlah),0) FROM pendapatan  WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?', [$bln,$thn], 'ii');
$pengBlIni = scalar($koneksi, 'SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?', [$bln,$thn], 'ii');
$saldo     = $pendBlIni - $pengBlIni;

render_header('Dashboard Admin', 'dashboard');
show_flash();
?>
<div class="page-header">
    <h4 class="mb-0"><i class="bi bi-speedometer2"></i> Selamat datang, <?= e($_SESSION['nama'] ?? $_SESSION['username']) ?></h4>
</div>

<!-- Stat utama -->
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card text-white bg-primary shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-buildings"></i> Total Kos</div><h3 class="mb-0 mt-1"><?= $totalKos ?></h3></div></div></div>
    <div class="col-md-3"><div class="card stat-card text-white bg-success shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-check2-circle"></i> Kos Tersedia</div><h3 class="mb-0 mt-1"><?= $totalTersedia ?></h3></div></div></div>
    <div class="col-md-3"><div class="card stat-card text-white bg-info shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-people"></i> Penyewa Aktif</div><h3 class="mb-0 mt-1"><?= $totalPenyewa ?></h3></div></div></div>
    <div class="col-md-3"><div class="card stat-card text-white <?= $saldo>=0?'bg-success':'bg-danger' ?> shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-wallet2"></i> Saldo Bulan Ini</div><h3 class="mb-0 mt-1"><?= rp($saldo) ?></h3></div></div></div>
</div>

<!-- Keuangan bulan ini -->
<div class="row g-3 mb-4">
    <div class="col-md-6"><div class="card border-success shadow-sm"><div class="card-body text-success"><div class="small">Pendapatan <?= date('M Y') ?></div><h4 class="mb-0"><?= rp($pendBlIni) ?></h4></div></div></div>
    <div class="col-md-6"><div class="card border-danger shadow-sm"><div class="card-body text-danger"><div class="small">Pengeluaran <?= date('M Y') ?></div><h4 class="mb-0"><?= rp($pengBlIni) ?></h4></div></div></div>
</div>

<!-- Daftar kos admin -->
<div class="d-flex justify-content-between align-items-center mb-2">
    <h5 class="mb-0"><i class="bi bi-buildings"></i> Kelola Kos</h5>
    <a href="tambah_kos.php" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah Kos</a>
</div>
<?php
$hasil = mysqli_query($koneksi, 'SELECT * FROM kos ORDER BY id DESC');
$rows  = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
?>
<div class="card shadow-sm">
    <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>ID</th><th>Nama</th><th>Tipe</th><th class="text-end">Harga</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kos. <a href="tambah_kos.php">Tambah sekarang</a>.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><a href="/detail.php?id=<?= (int)$r['id'] ?>" class="text-decoration-none fw-semibold"><?= e($r['nama_kos']) ?></a></td>
                <td><?= e($r['tipe_kamar']) ?></td>
                <td class="text-end"><?= rp((int)$r['harga_per_bulan']) ?></td>
                <td>
                    <?php if ($r['status']==='Tersedia'): ?>
                        <span class="badge bg-success rounded-pill">Tersedia</span>
                    <?php else: ?>
                        <span class="badge bg-secondary rounded-pill">Penuh</span>
                    <?php endif; ?>
                </td>
                <td class="text-center text-nowrap">
                    <a href="edit_kos.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="hapus_kos.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kos <?= e($r['nama_kos']) ?>?')"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php render_footer(); ?>
