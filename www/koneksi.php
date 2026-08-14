<?php
// koneksi.php - koneksi ke MariaDB (service name = host)
// Tugas 2 Cloud Computing - qullah
$koneksi = mysqli_connect('mariadb-qullah', 'kos_user', 'kos_pass123', 'db_kos');
if (!$koneksi) {
    http_response_code(500);
    die('Koneksi database gagal: ' . mysqli_connect_error());
}
mysqli_set_charset($koneksi, 'utf8mb4');
