<?php
// admin/hapus_penyewa.php - DELETE penyewa (bebas dihapus, tidak ada dependensi)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM penyewa WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    set_flash($ok ? 'success' : 'error', $ok ? 'Penyewa dihapus.' : 'Gagal menghapus penyewa.');
}
header('Location: penyewa.php'); exit;
