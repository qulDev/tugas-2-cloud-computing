<?php
// index.php - Homepage publik: daftar kos + pencarian/filter + toggle tabel/kartu (Soal 9)
require_once __DIR__ . '/partials.php';
require_once __DIR__ . '/koneksi.php';

// --- Build filter dinamis (B2) ---
$where  = [];
$params = [];
$types  = '';
$q     = trim($_GET['q']     ?? '');
$tipe  = trim($_GET['tipe']  ?? '');
$status= trim($_GET['status']?? '');
$min   = trim($_GET['min']   ?? '');
$max   = trim($_GET['max']   ?? '');

if ($q !== '')      { $where[] = 'k.nama_kos LIKE ?'; $params[] = "%$q%"; $types .= 's'; }
if ($tipe !== '')   { $where[] = 'k.tipe_kamar = ?';  $params[] = $tipe;  $types .= 's'; }
if ($status !== '') { $where[] = 'k.status = ?';      $params[] = $status;$types .= 's'; }
if ($min !== '' && ctype_digit($min)) { $where[] = 'k.harga_per_bulan >= ?'; $params[] = (int)$min; $types .= 'i'; }
if ($max !== '' && ctype_digit($max)) { $where[] = 'k.harga_per_bulan <= ?'; $params[] = (int)$max; $types .= 'i'; }

$sql = 'SELECT * FROM kos k';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY k.id DESC';

$stmt = mysqli_prepare($koneksi, $sql);
if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$rows = mysqli_fetch_all($hasil, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

render_header('Daftar Kos', 'home');
?>
<div class="page-header">
    <h4 class="mb-0"><i class="bi bi-buildings"></i> Daftar Kos Tersedia</h4>
</div>

<!-- Filter / Pencarian (B2) -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Cari nama kos</label>
                <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="cth: Melati">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Tipe</label>
                <select class="form-select" name="tipe">
                    <option value="">Semua</option>
                    <?php foreach (['Putra','Putri','Campur'] as $t): ?>
                        <option value="<?= $t ?>" <?= $tipe===$t?'selected':'' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select class="form-select" name="status">
                    <option value="">Semua</option>
                    <?php foreach (['Tersedia','Penuh'] as $s): ?>
                        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Harga min</label>
                <input type="number" min="0" step="1000" class="form-control" name="min" value="<?= e($min) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Harga max</label>
                <input type="number" min="0" step="1000" class="form-control" name="max" value="<?= e($max) ?>">
            </div>
            <div class="col-md-1 d-grid">
                <button class="btn btn-primary" type="submit" title="Cari"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Toggle view -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small"><?= count($rows) ?> kos ditemukan</div>
    <div class="btn-group btn-group-sm" role="group">
        <button id="btn-table" class="btn btn-outline-primary active" onclick="toggleView('table')"><i class="bi bi-list-ul"></i> Tabel</button>
        <button id="btn-card"  class="btn btn-outline-primary" onclick="toggleView('card')"><i class="bi bi-grid"></i> Kartu</button>
    </div>
</div>

<?php if (!$rows): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        Tidak ada kos yang cocok dengan filter.
    </div>
<?php else: ?>

<!-- VIEW 1: TABLE (default - 9 kolom, pemenuhan Soal 9) -->
<div id="table-view">
    <div class="card shadow-sm">
        <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Foto</th>
                    <th>Nama Kos</th>
                    <th>Alamat</th>
                    <th>Tipe</th>
                    <th class="text-end">Harga/bln</th>
                    <th class="text-center">Kamar</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <?php $foto = $r['foto'] ?: 'uploads/default.jpg'; ?>
                <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><img src="/<?= e($foto) ?>" class="thumb rounded" alt="foto"></td>
                    <td><a href="/detail.php?id=<?= (int)$r['id'] ?>" class="fw-semibold text-decoration-none"><?= e($r['nama_kos']) ?></a></td>
                    <td><span class="addr" title="<?= e($r['alamat']) ?>"><?= e($r['alamat']) ?></span></td>
                    <td><?= e($r['tipe_kamar']) ?></td>
                    <td class="text-end fw-semibold"><?= rp((int)$r['harga_per_bulan']) ?></td>
                    <td class="text-center"><?= (int)$r['jumlah_kamar'] ?></td>
                    <td>
                        <?php if ($r['status']==='Tersedia'): ?>
                            <span class="badge bg-success rounded-pill">Tersedia</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill">Penuh</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted"><?= tgl($r['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- VIEW 2: CARD (hidden by default) -->
<div id="card-view" style="display:none">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($rows as $r): ?>
            <?php $foto = $r['foto'] ?: 'uploads/default.jpg'; ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="/<?= e($foto) ?>" class="card-img-top" style="height:200px;object-fit:cover" alt="foto">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title mb-1"><?= e($r['nama_kos']) ?></h5>
                        <p class="text-muted small mb-2"><i class="bi bi-geo-alt"></i> <?= e($r['alamat']) ?></p>
                        <div class="mb-2">
                            <?php if ($r['status']==='Tersedia'): ?>
                                <span class="badge bg-success rounded-pill">Tersedia</span>
                            <?php else: ?>
                                <span class="badge bg-secondary rounded-pill">Penuh</span>
                            <?php endif; ?>
                            <span class="badge bg-light text-dark rounded-pill"><?= e($r['tipe_kamar']) ?></span>
                        </div>
                        <p class="card-text fw-bold fs-5 text-primary mb-3"><?= rp((int)$r['harga_per_bulan']) ?> <span class="fs-6 text-muted fw-normal">/bulan</span></p>
                        <a href="/detail.php?id=<?= (int)$r['id'] ?>" class="btn btn-outline-primary mt-auto"><i class="bi bi-eye"></i> Lihat Detail</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function toggleView(v) {
    var tabel = document.getElementById('table-view');
    var card  = document.getElementById('card-view');
    var bt = document.getElementById('btn-table');
    var bc = document.getElementById('btn-card');
    if (v === 'card') {
        tabel.style.display = 'none'; card.style.display = 'block';
        bt.classList.remove('active'); bc.classList.add('active');
    } else {
        tabel.style.display = 'block'; card.style.display = 'none';
        bt.classList.add('active'); bc.classList.remove('active');
    }
}
</script>
<?php render_footer(); ?>
