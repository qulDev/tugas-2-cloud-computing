<?php
// flash.php - helper flash message (Bootstrap alert) untuk feedback CRUD
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function set_flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function show_flash(): void {
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $map = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ];
    $cls = $map[$f['type']] ?? 'alert-info';
    echo '<div class="alert ' . $cls . ' alert-dismissible fade show mb-4" role="alert">'
       . htmlspecialchars($f['msg'])
       . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
       . '</div>';
}
