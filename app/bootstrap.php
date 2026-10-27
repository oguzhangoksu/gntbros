<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('DATA', ROOT . '/data');
define('UPLOADS', ROOT . '/uploads');

// XAMPP php.ini'de default_charset=ISO-8859-9 olabiliyor; bu proje baştan sona UTF-8
ini_set('default_charset', 'UTF-8');
date_default_timezone_set('Europe/Istanbul');
mb_internal_encoding('UTF-8');
ini_set('display_errors', '0');
error_reporting(E_ALL);

require APP . '/storage.php';
require APP . '/helpers.php';
require APP . '/views/partials.php';

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('gntsid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => base_path() . '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
