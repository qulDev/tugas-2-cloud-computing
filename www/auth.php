<?php
// auth.php - helper session & proteksi halaman admin (B1)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect ke login kalau belum login. Dipanggil di paling atas tiap /admin/*.php
function check_admin(): void {
    if (empty($_SESSION['admin'])) {
        header('Location: /login.php');
        exit;
    }
}
