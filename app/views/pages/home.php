<?php
/** @var array $data */
$hero = $data['hero'] ?? [];
$services = $data['services'] ?? [];
$about = $data['about'] ?? [];
$grinding = $data['grinding'] ?? [];
$sectors = $data['sectors'] ?? [];
$cta = $data['cta'] ?? [];
?>
<section class="hero">
    <div class="wrap hero-grid">
        <div class="hero-copy">
            <h1><?= e(t($hero['title'] ?? '')) ?></h1>
            <p class="lead"><?= e(t($hero['text'] ?? '')) ?></p>
            <div class="actions">
                <a class="btn btn-red" href="<?= e(url('contact')) ?>"><?= e(t($hero['button'] ?? '')) ?></a>
                <a class="btn btn-line" href="<?= e(url('applications')) ?>"><?= e(t($hero['button2'] ?? '')) ?></a>
            </div>
        </div>

        <figure class="hero-drawing">
            <?php if (!empty($hero['dim_x'])): ?>
                <div class="dim dim-x" aria-hidden="true"><span class="dim-line"></span><span class="dim-val"><?= e($hero['dim_x']) ?></span></div>
            <?php endif; ?>
            <div class="hero-media">
                <?php if (!empty($hero['video'])): ?>
                    <video src="<?= e(media($hero['video'])) ?>" <?= !empty($hero['poster']) ? 'poster="' . e(media($hero['poster'])) . '"' : '' ?> autoplay muted loop playsinline preload="metadata"></video>
                <?php elseif (!empty($hero['poster'])): ?>
                    <?= media_tag($hero['poster'], t($hero['title'] ?? ''), '', false) ?>
                <?php endif; ?>
            </div>
            <?php if (!empty($hero['dim_y'])): ?>
                <div class="dim dim-y" aria-hidden="true"><span class="dim-line"></span><span class="dim-val"><?= e($hero['dim_y']) ?></span></div>
            <?php endif; ?>
            <?php if (t($hero['dim_caption'] ?? '') !== ''): ?>
                <figcaption><?= e(t($hero['dim_caption'])) ?></figcaption>
            <?php endif; ?>
        </figure>
    </div>
</section>

<?= broach_divider() ?>

<?php if (!empty($services['items'])): ?>
<section class="section services">
    <div class="wrap">
        <h2 class="section-title"><?= e(t($services['title'] ?? '')) ?></h2>
        <div class="service-cols">
            <?php foreach ($services['items'] as $s): ?>
                <article class="service">
                    <?= media_tag($s['image'] ?? '', t($s['title']), 'service-img') ?>
                    <h3><?= e(t($s['title'])) ?></h3>
                    <p><?= e(t($s['text'])) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section about-home">
    <div class="wrap split">
        <div class="split-media framed">
            <?= media_tag($about['video'] ?? '', t($about['title'] ?? '')) ?>
        </div>
        <div class="split-copy">
            <h2><?= e(t($about['title'] ?? '')) ?></h2>
            <?= paragraphs(t($about['text'] ?? ''), 'lead') ?>
            <dl class="points">
                <?php foreach ($about['points'] ?? [] as $p): ?>
                    <dt><?= e(t($p['title'])) ?></dt>
                    <dd><?= e(t($p['text'])) ?></dd>
                <?php endforeach; ?>
            </dl>
            <a class="link-arrow" href="<?= e(url('about')) ?>"><?= e(__('about')) ?></a>
        </div>
    </div>
</section>

<?php if (!empty($grinding['items'])): ?>
<section class="section spec">
    <div class="wrap">
        <div class="section-head">
            <h2><?= e(t($grinding['title'] ?? '')) ?></h2>
            <p><?= e(t($grinding['text'] ?? '')) ?></p>
        </div>
        <div class="spec-table" role="table">
            <div class="spec-row spec-headrow" role="row">
                <div role="columnheader" class="sr-only-sm"></div>
                <div role="columnheader"><?= lang() === 'en' ? 'Method' : 'Yöntem' ?></div>
                <div role="columnheader"><?= e(__('capacity')) ?></div>
            </div>
            <?php foreach ($grinding['items'] as $g): ?>
                <div class="spec-row" role="row">
                    <div role="cell" class="spec-thumb"><?= media_tag($g['image'] ?? '', t($g['title'])) ?></div>
                    <div role="cell" class="spec-desc">
                        <h3><?= e(t($g['title'])) ?></h3>
                        <p><?= e(t($g['text'])) ?></p>
                    </div>
                    <div role="cell" class="spec-cap"><?= e(t($g['capacity'] ?? '')) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <a class="link-arrow" href="<?= e(url('applications')) ?>"><?= e(__('applications')) ?></a>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($sectors['items'])): ?>
<section class="section sectors dark">
    <div class="wrap split split-reverse">
        <div class="split-copy">
            <h2><?= e(t($sectors['title'] ?? '')) ?></h2>
            <ul class="tick-list cols-2">
                <?php foreach ($sectors['items'] as $s): ?>
                    <li><?= e(t($s['name'])) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if (!empty($sectors['video'])): ?>
            <div class="split-media framed"><?= media_tag($sectors['video'], t($sectors['title'] ?? '')) ?></div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<section class="section cta">
    <div class="wrap cta-grid">
        <div>
            <h2><?= e(t($cta['title'] ?? '')) ?></h2>
            <p class="lead"><?= e(t($cta['text'] ?? '')) ?></p>
            <a class="btn btn-red" href="<?= e(url('contact')) ?>"><?= e(t($cta['button'] ?? '')) ?></a>
        </div>
        <?php if (!empty($cta['image'])): ?>
            <div class="cta-img"><?= media_tag($cta['image'], t($cta['title'] ?? '')) ?></div>
        <?php endif; ?>
    </div>
</section>
