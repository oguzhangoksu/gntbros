<?php /** @var array $data */
$intro = $data['intro'] ?? [];
page_head(__('applications'), [__('applications') => null], t($intro['text'] ?? ''));
?>
<section class="section">
    <div class="wrap">
        <h2 class="section-title"><?= e(t($intro['title'] ?? '')) ?></h2>
        <?php foreach (active_items($data['items'] ?? []) as $i => $a): ?>
            <article class="app-row<?= $i % 2 ? ' is-flipped' : '' ?>" id="<?= e(slugify(t($a['title']))) ?>">
                <div class="app-media framed"><?= media_tag($a['media'] ?? '', t($a['title'])) ?></div>
                <div class="app-copy prose">
                    <h3 class="h2"><?= e(t($a['title'])) ?></h3>
                    <?php if (t($a['capacity'] ?? '') !== ''): ?>
                        <p class="capacity"><span><?= e(__('capacity')) ?></span> <?= e(t($a['capacity'])) ?></p>
                    <?php endif; ?>
                    <?= paragraphs(t($a['text'] ?? '')) ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
