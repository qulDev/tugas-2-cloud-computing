<?php
// admin/hapus_kos.php - DELETE kos dengan pre-check BR-4 (tolak jika masih ada penyewa)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: dashboard.php'); exit; }

// BR-4: pre-check penyewa
$stmt = mysqli_prepare($koneksi, 'SELECT COUNT(*) FROM penyewa WHERE kos_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$jml = (int)mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];
mysqli_stmt_close($stmt);

if ($jml > 0) {
    set_flash('error', "Tidak bisa menghapus kos ini karena masih ada $jml penyewa. Hapus/pindahkan penyewa terlebih dahulu.");
    header('Location: dashboard.php'); exit;
}

// Ambil foto sebelum hapus
$stmt = mysqli_prepare($koneksi, 'SELECT foto FROM kos WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$f = mysqli_fetch_row(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$f) { header('Location: dashboard.php'); exit; }

$stmt = mysqli_prepare($koneksi, 'DELETE FROM kos WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($ok) {
    $foto = $f[0];
    if ($foto && $foto !== 'uploads/default.jpg' && is_file(__DIR__ . '/../' . $foto)) {
        unlink(__DIR__ . '/../' . $foto);
    }
    set_flash('success', 'Kos berhasil dihapus.');
} else {
    set_flash('error', 'Gagal menghapus kos. (Mungkin masih direferensikan data lain.)');
}
header('Location: dashboard.php'); exit;
