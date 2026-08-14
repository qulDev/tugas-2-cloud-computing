<?php
// admin/laporan.php - laporan keuangan bulanan (stat card + breakdown kategori + chart)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$bln = (int)($_GET['bulan'] ?? date('n'));
$thn = (int)($_GET['tahun'] ?? date('Y'));

function totalPeriode($koneksi, string $tabel, int $bln, int $thn): int {
    $stmt = mysqli_prepare($koneksi, "SELECT COALESCE(SUM(jumlah),0) FROM $tabel WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?");
    mysqli_stmt_bind_param($stmt, 'ii', $bln, $thn);
    mysqli_stmt_execute($stmt);
    $v = (int)mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];
    mysqli_stmt_close($stmt);
    return $v;
}
function breakdown($koneksi, string $tipe, int $bln, int $thn): array {
    $sql = "SELECT kat.nama, COALESCE(SUM(t.jumlah),0) AS total
            FROM kategori kat
            LEFT JOIN " . ($tipe === 'Pendapatan' ? 'pendapatan' : 'pengeluaran') . " t
                   ON kat.id = t.kategori_id AND MONTH(t.tanggal)=? AND YEAR(t.tanggal)=?
            WHERE kat.tipe = ?
            GROUP BY kat.id, kat.nama
            HAVING total > 0
            ORDER BY total DESC";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'iis', $bln, $thn, $tipe);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

$totalPend = totalPeriode($koneksi, 'pendapatan', $bln, $thn);
$totalPeng = totalPeriode($koneksi, 'pengeluaran', $bln, $thn);
$saldo     = $totalPend - $totalPeng;
$bkPend    = breakdown($koneksi, 'Pendapatan',  $bln, $thn);
$bkPeng    = breakdown($koneksi, 'Pengeluaran', $bln, $thn);
$adaData   = ($totalPend + $totalPeng) > 0;

render_header('Laporan Keuangan', 'laporan');
show_flash();
?>
<div class="page-header"><h4 class="mb-0"><i class="bi bi-bar-chart"></i> Laporan Keuangan Bulanan</h4></div>

<form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-auto">
        <label class="form-label small text-muted mb-1">Bulan</label>
        <select name="bulan" class="form-select form-select-sm">
            <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?= $m ?>" <?= $m===$bln?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <label class="form-label small text-muted mb-1">Tahun</label>
        <select name="tahun" class="form-select form-select-sm">
            <?php foreach([date('Y')-1,date('Y'),date('Y')+1] as $y): ?>
                <option value="<?= $y ?>" <?= (int)$y===$thn?'selected':'' ?>><?= $y ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Tampilkan</button></div>
</form>

<?php if (!$adaData): ?>
    <div class="text-center text-muted py-5"><i class="bi bi-inbox fs-1 d-block mb-2"></i> Belum ada transaksi pada periode ini.</div>
<?php else: ?>

<!-- Stat card -->
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card text-white bg-success shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-arrow-down-circle"></i> Total Pendapatan</div><h3 class="mb-0 mt-1"><?= rp($totalPend) ?></h3></div></div></div>
    <div class="col-md-4"><div class="card stat-card text-white bg-danger shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-arrow-up-circle"></i> Total Pengeluaran</div><h3 class="mb-0 mt-1"><?= rp($totalPeng) ?></h3></div></div></div>
    <div class="col-md-4"><div class="card stat-card text-white <?= $saldo>=0?'bg-primary':'bg-secondary' ?> shadow-sm"><div class="card-body"><div class="small"><i class="bi bi-wallet2"></i> Saldo</div><h3 class="mb-0 mt-1 <?= $saldo<0?'text-warning':'' ?>"><?= rp($saldo) ?></h3></div></div></div>
</div>

<div class="row g-4">
    <!-- Breakdown Pendapatan -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light"><i class="bi bi-arrow-down-circle text-success"></i> Pendapatan per Kategori</div>
            <div class="card-body">
                <?php if (!$bkPend): ?>
                    <p class="text-muted mb-0">Tidak ada pendapatan tercatat.</p>
                <?php else: ?>
                <div class="row">
                    <div class="col-md-7"><table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Kategori</th><th class="text-end">Jumlah</th><th class="text-end">%</th></tr></thead>
                        <tbody>
                        <?php foreach ($bkPend as $b): $pct = $totalPend>0 ? round($b['total']*100/$totalPend,1) : 0; ?>
                            <tr><td><?= e($b['nama']) ?></td><td class="text-end"><?= rp((int)$b['total']) ?></td><td class="text-end text-muted"><?= $pct ?>%</td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <div class="col-md-5"><canvas id="chartPend" height="180"></canvas></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Breakdown Pengeluaran -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light"><i class="bi bi-arrow-up-circle text-danger"></i> Pengeluaran per Kategori</div>
            <div class="card-body">
                <?php if (!$bkPeng): ?>
                    <p class="text-muted mb-0">Tidak ada pengeluaran tercatat.</p>
                <?php else: ?>
                <div class="row">
                    <div class="col-md-7"><table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Kategori</th><th class="text-end">Jumlah</th><th class="text-end">%</th></tr></thead>
                        <tbody>
                        <?php foreach ($bkPeng as $b): $pct = $totalPeng>0 ? round($b['total']*100/$totalPeng,1) : 0; ?>
                            <tr><td><?= e($b['nama']) ?></td><td class="text-end"><?= rp((int)$b['total']) ?></td><td class="text-end text-muted"><?= $pct ?>%</td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table></div>
                    <div class="col-md-5"><canvas id="chartPeng" height="180"></canvas></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
function pie(id, labels, data, colors) {
    var ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, { type: 'doughnut', data: { labels: labels, datasets: [{ data: data, backgroundColor: colors }] },
        options: { plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } } });
}
<?php if ($bkPend): ?>
pie('chartPend',
    <?= json_encode(array_column($bkPend, 'nama')) ?>,
    <?= json_encode(array_map('intval', array_column($bkPend, 'total'))) ?>,
    ['#198754','#20c997','#0dcaf0','#6f42c1','#6610f2','#fd7e14','#ffc107','#adb5bd','#61dafb','#83cf46']);
<?php endif; ?>
<?php if ($bkPeng): ?>
pie('chartPeng',
    <?= json_encode(array_column($bkPeng, 'nama')) ?>,
    <?= json_encode(array_map('intval', array_column($bkPeng, 'total'))) ?>,
    ['#dc3545','#e35d6a','#fd7e14','#ffc107','#6f42c1','#d63384','#6c757d','#ad2e3a','#b02a37','#c7838c']);
<?php endif; ?>
</script>
<?php endif; ?>
<?php render_footer(); ?>
