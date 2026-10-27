<?php
declare(strict_types=1);

/**
 * Broş diş profili: kaba talaş dişlerinden ince dişlere doğru kademeli yükselen testere dişi.
 * Bölüm ayırıcı olarak kullanılır.
 */
function broach_divider(string $class = ''): string
{
    $teeth = 64;
    $w = 24;
    $d = 'M0 40';
    for ($i = 0; $i < $teeth; $i++) {
        $x = $i * $w;
        // Diş yüksekliği ilk 2/3'te artar (kesme dişleri), sonra sabitlenir (kalibre dişleri)
        $h = 10 + min($i, (int) ($teeth * 0.66)) * 0.35;
        $d .= sprintf(' L%d %.1f L%d %.1f L%d 40', $x + 4, 40 - $h, $x + $w - 3, 40 - $h + 3, $x + $w - 3);
        $d .= sprintf(' L%d 40', $x + $w);
    }
    $d .= ' L' . ($teeth * $w) . ' 48 L0 48 Z';
    return '<div class="broach ' . e($class) . '" aria-hidden="true"><svg viewBox="0 0 ' . ($teeth * $w) . ' 48" preserveAspectRatio="none"><path d="' . $d . '"/></svg></div>';
}

function page_head(string $title, array $crumbs = [], string $lead = ''): void
{
    ?>
    <section class="page-head">
        <div class="wrap">
            <nav class="crumbs" aria-label="breadcrumb">
                <a href="<?= e(url('home')) ?>"><?= e(__('home')) ?></a>
                <?php foreach ($crumbs as $label => $href): ?>
                    <span aria-hidden="true">/</span>
                    <?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <h1><?= e($title) ?></h1>
            <?php if ($lead !== ''): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
        </div>
        <?= broach_divider('on-dark') ?>
    </section>
    <?php
}

/** Görsel ya da video etiketi üretir */
function media_tag(string $path, string $alt = '', string $class = '', bool $lazy = true): string
{
    if ($path === '') {
        return '';
    }
    if (is_video($path)) {
        return '<video class="' . e($class) . '" src="' . e(media($path)) . '" autoplay muted loop playsinline preload="metadata" aria-label="' . e($alt) . '"></video>';
    }
    return '<img class="' . e($class) . '" src="' . e(media($path)) . '" alt="' . e($alt) . '"' . ($lazy ? ' loading="lazy" decoding="async"' : '') . '>';
}

function brand_mark(): string
{
    $s = settings()['site'] ?? [];
    if (!empty($s['logo'])) {
        return '<img src="' . e(media($s['logo'])) . '" alt="' . e($s['name'] ?? '') . '">';
    }
    // Panelden logo yüklenmediyse yazı logosu
    $name = e($s['name'] ?? 'GNT Broş');
    $parts = explode(' ', $name, 2);
    return '<span class="wordmark"><svg class="wordmark-icon" viewBox="0 0 28 28" aria-hidden="true"><path d="M2 24 L6 24 L6 16 L12 18 L12 24 L14 24 L14 11 L20 13 L20 24 L22 24 L22 5 L27 7 L27 24 L27 27 L2 27 Z"/></svg>'
        . '<span class="wordmark-text"><b>' . $parts[0] . '</b>' . (isset($parts[1]) ? ' ' . $parts[1] : '') . '</span></span>';
}
