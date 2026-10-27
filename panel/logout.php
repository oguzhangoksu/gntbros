<?php
declare(strict_types=1);

require __DIR__ . '/lib.php';

// Çıkış yalnızca POST + CSRF ile (bağlantıyla dışarıdan çıkış yaptırılamasın)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check($_POST['_token'] ?? null)) {
    logout();
}
header('Location: login.php');
