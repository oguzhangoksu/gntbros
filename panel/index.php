<?php
declare(strict_types=1);

require_once __DIR__ . '/lib.php';

$user = require_admin();
$sections = panel_sections();
$p = (string) ($_GET['p'] ?? 'dashboard');
$pages = ['dashboard' => 'Özet'] + array_map(fn($s) => $s['title'], $sections) + ['messages' => 'Mesajlar', 'media' => 'Medya'];
if (!isset($pages[$p])) {
    $p = 'dashboard';
}
$unread = unread_count();
$siteLinks = ['home' => url('home', '', 'tr'), 'pages' => url('about', '', 'tr'), 'applications' => url('applications', '', 'tr'), 'products' => url('products', '', 'tr'), 'gallery' => url('gallery', '', 'tr'), 'settings' => url('contact', '', 'tr')];
$messages = $p === 'messages' || $p === 'dashboard' ? Store::get('messages') : [];

$nav = function (string $key, string $label, string $extra = '') use ($p): string {
    return '<li><a href="?p=' . e($key) . '"' . ($p === $key ? ' aria-current="page"' : '') . '>' . e($label) . $extra . '</a></li>';
};
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <title><?= e($pages[$p]) ?> | Panel</title>
    <link rel="stylesheet" href="assets/panel.css?v=<?= filemtime(__DIR__ . '/assets/panel.css') ?>">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <a class="sb-brand" href="./"><?= brand_mark() ?><small>Panel</small></a>
        <button class="sb-toggle" type="button" aria-expanded="false" aria-controls="sb-nav">Menü</button>
        <nav id="sb-nav">
            <ul>
                <?= $nav('dashboard', 'Özet') ?>
            </ul>
            <p class="sb-label">İçerik</p>
            <ul>
                <?php foreach ($sections as $k => $s): if ($k === 'settings') continue; ?>
                    <?= $nav($k, $s['title']) ?>
                <?php endforeach; ?>
                <?= $nav('media', 'Medya kütüphanesi') ?>
            </ul>
            <p class="sb-label">Gelen kutusu</p>
            <ul>
                <?= $nav('messages', 'Mesajlar', $unread ? ' <span class="badge" data-unread>' . $unread . '</span>' : ' <span class="badge" data-unread hidden>0</span>') ?>
            </ul>
            <p class="sb-label">Sistem</p>
            <ul>
                <?= $nav('settings', $sections['settings']['title']) ?>
            </ul>
        </nav>
        <div class="sb-foot">
            <a href="<?= e(base_path()) ?>/" target="_blank" rel="noopener">Siteyi görüntüle</a>
            <form method="post" action="logout.php"><input type="hidden" name="_token" value="<?= e(csrf_token()) ?>"><button type="submit" class="linklike">Çıkış yap (<?= e($user) ?>)</button></form>
        </div>
    </aside>

    <main class="main">
<?php if (isset($sections[$p])): $sec = $sections[$p]; ?>
        <header class="page-bar">
            <div>
                <h1><?= e($sec['title']) ?></h1>
                <div class="lang-switch" role="radiogroup" aria-label="Düzenlenen dil">
                    <span class="lang-switch-label">Düzenlenen dil:</span>
                    <button type="button" role="radio" data-lang="tr" aria-checked="true">Türkçe</button>
                    <button type="button" role="radio" data-lang="en" aria-checked="false">English <span class="missing" hidden></span></button>
                </div>
            </div>
            <div class="bar-actions">
                <?php if (isset($siteLinks[$p])): ?><a class="btn btn-ghost" href="<?= e($siteLinks[$p]) ?>" target="_blank" rel="noopener">Sitede gör</a><?php endif; ?>
                <button class="btn btn-primary save" type="button" form="editor">Kaydet</button>
            </div>
        </header>
        <p class="lang-hint" data-for="en" hidden>İngilizce alanları düzenliyorsunuz. Boş bıraktığınız alanlarda sitenin İngilizce sürümünde Türkçe metin gösterilir; kutudaki silik yazı o Türkçe metindir.</p>
        <form id="editor" class="editor scope" data-section="<?= e($p) ?>" novalidate>
            <?= render_editor($sec['fields'], Store::get($sec['file'])) ?>
            <?php if ($p === 'settings'): ?>
                <div class="card smtp-test" data-move-to="smtp">
                    <h3>SMTP denemesi</h3>
                    <p class="muted">Ayarları kaydettikten sonra alıcı adresine bir deneme e-postası gönderin.</p>
                    <button type="button" class="btn test-mail">Deneme e-postası gönder</button>
                    <pre class="mail-log" hidden></pre>
                </div>
            <?php endif; ?>
        </form>

<?php elseif ($p === 'messages'): ?>
        <header class="page-bar"><div><h1>Mesajlar</h1><p class="muted">İletişim formundan gelen mesajlar. Yanıtla butonu e-posta programınızı açar.</p></div></header>
        <?php if (!$messages): ?>
            <div class="card empty">Henüz mesaj yok. İletişim formu doldurulduğunda mesajlar burada listelenir.</div>
        <?php endif; ?>
        <div class="messages">
            <?php foreach ($messages as $m): ?>
                <article class="msg<?= empty($m['read']) ? ' unread' : '' ?>" data-id="<?= e($m['id']) ?>">
                    <header class="msg-head">
                        <div>
                            <strong><?= e($m['name']) ?></strong><?= $m['company'] ? ' – ' . e($m['company']) : '' ?>
                            <span class="muted"><?= e(date('d.m.Y H:i', strtotime($m['date']))) ?> · <?= strtoupper(e($m['lang'] ?? 'tr')) ?></span>
                        </div>
                        <div class="msg-tools">
                            <a class="btn btn-sm" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['subject']) ?>">Yanıtla</a>
                            <button type="button" class="btn btn-sm btn-ghost msg-read"><?= empty($m['read']) ? 'Okundu işaretle' : 'Okunmadı işaretle' ?></button>
                            <button type="button" class="btn btn-sm btn-danger msg-del">Sil</button>
                        </div>
                    </header>
                    <p class="msg-subject"><?= e($m['subject']) ?></p>
                    <p class="msg-meta"><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a><?= $m['phone'] ? ' · <a href="' . e(tel_href($m['phone'])) . '">' . e($m['phone']) . '</a>' : '' ?>
                        <?php if (($m['mail'] ?? null) === 'failed'): ?><span class="tag tag-warn">E-posta gönderilemedi</span><?php endif; ?></p>
                    <div class="msg-body"><?= nl2br(e($m['message']), false) ?></div>
                </article>
            <?php endforeach; ?>
        </div>

<?php elseif ($p === 'media'): ?>
        <header class="page-bar">
            <div><h1>Medya kütüphanesi</h1><p class="muted">Görseller en fazla 8 MB, videolar en fazla 60 MB olabilir. Kullanımdaki dosyalar silinemez.</p></div>
            <label class="btn btn-primary">Dosya yükle<input type="file" class="lib-upload" accept="image/*,video/mp4,video/webm" multiple hidden></label>
        </header>
        <div class="lib-grid media-page">
            <?php foreach (upload_files() as $file): ?>
                <figure class="lib-item" data-path="<?= e($file['path']) ?>">
                    <div class="lib-thumb"><?= preview_html($file['path']) ?></div>
                    <figcaption><span title="<?= e($file['path']) ?>"><?= e(basename($file['path'])) ?></span><small><?= human_size($file['size']) ?></small></figcaption>
                    <div class="lib-tools">
                        <button type="button" class="btn btn-sm btn-ghost copy-path">Yolu kopyala</button>
                        <button type="button" class="btn btn-sm btn-danger del-file">Sil</button>
                    </div>
                </figure>
            <?php endforeach; ?>
        </div>


<?php else: ?>
        <header class="page-bar"><div><h1>Hoş geldiniz</h1><p class="muted">Sitedeki tüm içerik bu panelden yönetilir. Değişiklikler kaydettiğiniz anda yayına girer.</p></div></header>
        <div class="dash">
            <a class="card dash-card" href="?p=messages"><strong><?= $unread ?></strong><span>okunmamış mesaj</span></a>
            <a class="card dash-card" href="?p=products"><strong><?= count(Store::get('products')['items'] ?? []) ?></strong><span>ürün</span></a>
            <a class="card dash-card" href="?p=gallery"><strong><?= count(Store::get('gallery')['items'] ?? []) ?></strong><span>galeri görseli</span></a>
            <a class="card dash-card" href="?p=media"><strong><?= count(upload_files()) ?></strong><span>medya dosyası</span></a>
        </div>
        <?php if (empty(settings()['smtp']['enabled'])): ?>
            <div class="alert alert-info">İletişim formu mesajları panele kaydediliyor ancak e-posta ile gönderilmiyor. Açmak için <a href="?p=settings">Genel ayarlar › E-posta gönderimi (SMTP)</a> bölümünü doldurun.</div>
        <?php endif; ?>
        <?php if ($messages): ?>
            <h2 class="h2">Son mesajlar</h2>
            <ul class="card recent">
                <?php foreach (array_slice($messages, 0, 5) as $m): ?>
                    <li class="<?= empty($m['read']) ? 'unread' : '' ?>"><a href="?p=messages"><strong><?= e($m['name']) ?></strong> – <?= e($m['subject']) ?></a><span class="muted"><?= e(date('d.m.Y H:i', strtotime($m['date']))) ?></span></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
<?php endif; ?>
    </main>
</div>

<div class="modal" id="library" hidden role="dialog" aria-modal="true" aria-labelledby="lib-title">
    <div class="modal-box">
        <header><h2 id="lib-title">Kütüphaneden seç</h2><button type="button" class="modal-close" aria-label="Kapat">×</button></header>
        <div class="lib-grid picker"></div>
    </div>
</div>


<script src="<?= e(asset('js/jquery-3.6.2.min.js')) ?>"></script>
<script src="assets/vendor/sweetalert2.min.js"></script>
<script src="assets/panel.js?v=<?= filemtime(__DIR__ . '/assets/panel.js') ?>"></script>
</body>
</html>
