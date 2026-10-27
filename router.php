<?php
// Yalnızca geliştirme içindir: php -S localhost:8080 router.php
// Canlıda Apache + .htaccess kuralları aynı korumaları sağlar.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(app|data)(/|$)#', $path)
    || preg_match('#^/panel/(lib|schemas)\.php$#', $path)
    || preg_match('#^/uploads/.*\.(php\d?|phtml|phar|html?|svg)$#i', $path)
    || preg_match('#(^|/)\.#', $path)) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}
if ($path === '/panel') {
    header('Location: /panel/', true, 301);
    return true;
}
if ($path === '/panel/') {
    $_SERVER['SCRIPT_NAME'] = '/panel/index.php';
    require __DIR__ . '/panel/index.php';
    return true;
}
require __DIR__ . '/index.php';
