<?php
/** @var array $settings @var array $seo @var array $alt @var ?string $pageKey */
$site = $settings['site'] ?? [];
$contact = $settings['contact'] ?? [];
$title = t($seo['title'] ?? '') ?: ($site['name'] ?? '');
$desc = t($seo['description'] ?? '');
$phone = $contact['phones'][0]['number'] ?? '';
$ogImage = !empty($seo['image']) ? $seo['image'] : (Store::get('home')['hero']['poster'] ?? '');
$isCorporate = in_array($pageKey, ['about', 'vision', 'policy'], true);
$navActive = fn(string ...$keys) => in_array($pageKey, $keys, true) || ($pageKey === 'product' && in_array('products', $keys, true)) ? ' aria-current="page"' : '';
?>
<!DOCTYPE html>
<html lang="<?= e(lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <?php if ($desc !== ''): ?><meta name="description" content="<?= e($desc) ?>"><?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($title) ?>">
    <?php if ($desc !== ''): ?><meta property="og:description" content="<?= e($desc) ?>"><?php endif; ?>
    <?php if ($ogImage !== '' && !is_video($ogImage)): ?><meta property="og:image" content="<?= e(site_url($ogImage)) ?>"><?php endif; ?>
    <?php foreach ($alt as $l => $href): ?>
        <link rel="alternate" hreflang="<?= e($l) ?>" href="<?= e(site_url(ltrim(substr($href, strlen(base_path())), '/'))) ?>">
    <?php endforeach; ?>
    <?php foreach ($relLinks ?? [] as $rel => $href): ?>
        <link rel="<?= e($rel) ?>" href="<?= e($href) ?>">
    <?php endforeach; ?>
    <?php if (!empty($site['favicon'])): ?><link rel="icon" href="<?= e(media($site['favicon'])) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Barlow:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
    <script>document.documentElement.classList.add("js")</script>
</head>
<body class="page-<?= e($pageKey ?? 'error') ?>">
<a class="skip" href="#main"><?= lang() === 'en' ? 'Skip to content' : 'İçeriğe geç' ?></a>

<header class="site-header">
    <div class="wrap header-row">
        <a class="brand" href="<?= e(url('home')) ?>" aria-label="<?= e($site['name'] ?? '') ?>"><?= brand_mark() ?></a>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
            <span class="nav-toggle-bars" aria-hidden="true"></span><span class="sr-only"><?= e(__('menu')) ?></span>
        </button>

        <nav class="site-nav" id="site-nav" aria-label="<?= e(__('menu_label')) ?>">
            <ul class="nav-list">
                <li><a href="<?= e(url('home')) ?>"<?= $navActive('home') ?>><?= e(__('home')) ?></a></li>
                <li class="has-sub<?= $isCorporate ? ' is-current' : '' ?>">
                    <button type="button" class="sub-toggle" aria-expanded="false"><?= e(__('corporate')) ?></button>
                    <ul class="sub">
                        <li><a href="<?= e(url('about')) ?>"<?= $navActive('about') ?>><?= e(__('about')) ?></a></li>
                        <li><a href="<?= e(url('vision')) ?>"<?= $navActive('vision') ?>><?= e(__('vision')) ?></a></li>
                        <li><a href="<?= e(url('policy')) ?>"<?= $navActive('policy') ?>><?= e(__('policy')) ?></a></li>
                    </ul>
                </li>
                <li><a href="<?= e(url('applications')) ?>"<?= $navActive('applications') ?>><?= e(__('applications')) ?></a></li>
                <li><a href="<?= e(url('products')) ?>"<?= $navActive('products') ?>><?= e(__('products')) ?></a></li>
                <li><a href="<?= e(url('gallery')) ?>"<?= $navActive('gallery') ?>><?= e(__('gallery')) ?></a></li>
                <li><a href="<?= e(url('contact')) ?>"<?= $navActive('contact') ?>><?= e(__('contact')) ?></a></li>
            </ul>
            <div class="nav-extra">
                <div class="lang-switch">
                    <?php foreach (LANGS as $l): ?>
                        <a href="<?= e($alt[$l] ?? url('home', '', $l)) ?>" hreflang="<?= $l ?>" lang="<?= $l ?>"<?= $l === lang() ? ' aria-current="true"' : '' ?>><?= strtoupper($l) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php if ($phone !== ''): ?>
                    <a class="btn btn-red btn-sm" href="<?= e(tel_href($phone)) ?>"><svg class="i" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25 11.4 11.4 0 0 0 3.6.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1z"/></svg><?= e($phone) ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main id="main">
