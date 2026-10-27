<?php
declare(strict_types=1);

require __DIR__ . '/lib.php';

if (admin_user() !== null) {
    header('Location: ./');
    exit;
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mb_substr(trim((string) ($_POST['username'] ?? '')), 0, 60);
    if (!csrf_check($_POST['_token'] ?? null)) {
        $error = 'Formun süresi doldu. Tekrar deneyin.';
    } else {
        $r = attempt_login($username, (string) ($_POST['password'] ?? ''));
        if ($r['ok']) {
            header('Location: ./');
            exit;
        }
        $error = $r['message'];
    }
}
$site = settings()['site'] ?? [];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Panel girişi | <?= e($site['name'] ?? '') ?></title>
    <link rel="stylesheet" href="<?= e(base_path()) ?>/panel/assets/panel.css?v=<?= filemtime(__DIR__ . '/assets/panel.css') ?>">
</head>
<body class="login-page">
<main class="login-box">
    <p class="login-brand"><?= brand_mark() ?></p>
    <h1>Yönetim paneli</h1>
    <?php if ($error): ?><div class="alert alert-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="on">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <label class="lbl" for="u">Kullanıcı adı</label>
        <input id="u" name="username" type="text" required autofocus autocomplete="username" value="<?= e($username) ?>">
        <label class="lbl" for="p">Şifre</label>
        <input id="p" name="password" type="password" required autocomplete="current-password">
        <button class="btn btn-primary btn-block" type="submit">Giriş yap</button>
    </form>
    <a class="back" href="<?= e(base_path()) ?>/">Siteye dön</a>
</main>
</body>
</html>
