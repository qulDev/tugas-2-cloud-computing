<?php
// partials.php - layout bersama (head, navbar, footer) agar tiap page ramping
require_once __DIR__ . '/auth.php';

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function rp(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}

function tgl(?string $d): string {
    if (!$d) return '-';
    $ts = strtotime($d);
    return $ts ? date('j M Y', $ts) : e($d);
}

function render_header(string $title = 'Manajemen Kos-Kosan', string $active = ''): void {
    $logged = !empty($_SESSION['admin']);
    $u = !empty($_SESSION['username']) ? e($_SESSION['username']) : '';
    $nav = function (string $href, string $label, string $key) use ($active) {
        $cls = ($active === $key) ? ' active' : '';
        echo '<li class="nav-item"><a class="nav-link' . $cls . '" href="' . $href . '">' . $label . '</a></li>';
    };
    ?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> &middot; Kos Qullah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/index.php">
            <i class="bi bi-house-door-fill"></i> Kos Qullah
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php $nav('/index.php', 'Beranda', 'home'); ?>
                <?php if ($logged): ?>
                    <?php $nav('/admin/dashboard.php', 'Dashboard', 'dashboard'); ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($active, ['kos','penyewa','kategori','pendapatan','pengeluaran','laporan']) ? 'active' : '' ?>" data-bs-toggle="dropdown" href="#">Manajemen</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/admin/penyewa.php"><i class="bi bi-people"></i> Penyewa</a></li>
                            <li><a class="dropdown-item" href="/admin/kategori.php"><i class="bi bi-tags"></i> Kategori Keuangan</a></li>
                            <li><a class="dropdown-item" href="/admin/pendapatan.php"><i class="bi bi-arrow-down-circle"></i> Pendapatan</a></li>
                            <li><a class="dropdown-item" href="/admin/pengeluaran.php"><i class="bi bi-arrow-up-circle"></i> Pengeluaran</a></li>
                            <li><a class="dropdown-item" href="/admin/laporan.php"><i class="bi bi-bar-chart"></i> Laporan Bulanan</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($logged): ?>
                    <li class="nav-item"><span class="nav-link text-light"><i class="bi bi-person-circle"></i> <?= $u ?></span></li>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm ms-2 my-1" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm ms-2 my-1" href="/login.php"><i class="bi bi-box-arrow-in-right"></i> Login Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
<?php
}

function render_footer(): void {
    ?>
</main>
<footer class="bg-light py-3 mt-5 text-center text-muted small">
    &copy; 2026 M. Rizqullah &middot; Tugas 2 Cloud Computing &middot; Platform as a Service Berbasis Podman
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
