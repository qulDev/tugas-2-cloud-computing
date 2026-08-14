<?php
// admin/pendapatan.php - daftar pendapatan (JOIN kos+kategori, filter bulan/tahun)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$bln = (int)($_GET['bulan'] ?? date('n'));
$thn = (int)($_GET['tahun'] ?? date('Y'));
$where = ' WHERE MONTH(p.tanggal)=? AND YEAR(p.tanggal)=? ';
$stmt = mysqli_prepare($koneksi, "SELECT p.*, k.nama_kos, kat.nama AS kategori
    FROM pendapatan p JOIN kos k ON p.kos_id=k.id JOIN kategori kat ON p.kategori_id=kat.id"
    . $where . " ORDER BY p.tanggal DESC, p.id DESC");
mysqli_stmt_bind_param($stmt, 'ii', $bln, $thn);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$total = array_sum(array_column($rows, 'jumlah'));

render_header('Pendapatan', 'pendapatan');
show_flash();
?>
<div class="page-header">
    <h4 class="mb-0"><i class="bi bi-arrow-down-circle"></i> Pendapatan</h4>
    <a href="tambah_pendapatan.php" class="btn btn-primary btn-sm ms-auto"><i class="bi bi-plus-lg"></i> Tambah Pendapatan</a>
</div>

<form method="get" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label class="form-label small text-muted mb-1">Bulan</label>
        <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m===$bln?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <label class="form-label small text-muted mb-1">Tahun</label>
        <select name="tahun" class="form-select form-select-sm" onchange="this.form.submit()">
            <?php foreach([date('Y')-1,date('Y'),date('Y')+1] as $y): ?>
                <option value="<?= $y ?>" <?= (int)$y===$thn?'selected':'' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <a href="pendapatan.php" class="btn btn-sm btn-outline-secondary">Reset</a>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Tanggal</th><th>Kos</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Jumlah</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pendapatan pada periode ini.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?= tgl($r['tanggal']) ?></td>
                <td><?= e($r['nama_kos']) ?></td>
                <td><span class="badge bg-success"><?= e($r['kategori']) ?></span></td>
                <td><?= e($r['keterangan'] ?: '-') ?></td>
                <td class="text-end fw-bold text-success"><?= rp((int)$r['jumlah']) ?></td>
                <td class="text-center text-nowrap">
                    <a href="edit_pendapatan.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    <a href="hapus_pendapatan.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus transaksi ini?')"><i class="bi bi-trash"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
            <tr class="table-light">
                <td colspan="4" class="fw-bold">Total <?= date('F Y', mktime(0,0,0,$bln,1,$thn)) ?></td>
                <td class="text-end fw-bold fs-6 text-success"><?= rp($total) ?></td>
                <td></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
<?php render_footer(); ?>
