<?php
// admin/hapus_pendapatan.php - DELETE pendapatan (bebas dihapus)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    $stmt = mysqli_prepare($koneksi, 'DELETE FROM pendapatan WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    set_flash($ok ? 'success' : 'error', $ok ? 'Pendapatan dihapus.' : 'Gagal menghapus.');
}
header('Location: pendapatan.php'); exit;
