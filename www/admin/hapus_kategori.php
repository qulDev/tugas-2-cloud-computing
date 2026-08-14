<?php
// admin/hapus_kategori.php - DELETE kategori dengan pre-check BR-9 (2 tabel)
require_once __DIR__ . '/../partials.php';
require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../flash.php';
check_admin();

$id = (int)($_GET['id'] ?? 0);
if ($id > 0) {
    // BR-9: cek 2 tabel (pendapatan + pengeluaran)
    $c1 = (int)mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pendapatan  WHERE kategori_id = $id"))[0];
    $c2 = (int)mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pengeluaran WHERE kategori_id = $id"))[0];
    $total = $c1 + $c2;
    if ($total > 0) {
        set_flash('error', "Kategori ini masih dipakai $total transaksi (pendapatan/pengeluaran). Hapus/ubah transaksi tersebut dulu.");
    } else {
        $stmt = mysqli_prepare($koneksi, 'DELETE FROM kategori WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        set_flash($ok ? 'success' : 'error', $ok ? 'Kategori dihapus.' : 'Gagal menghapus kategori.');
    }
}
header('Location: kategori.php'); exit;
