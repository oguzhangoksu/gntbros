<?php /** @var array $data */ page_head(__('policy'), [__('corporate') => '', __('policy') => null]); ?>
<section class="section">
    <div class="wrap split">
        <div class="split-copy prose">
            <h2><?= e(t($data['title'] ?? '')) ?></h2>
            <?= paragraphs(t($data['text'] ?? '')) ?>
        </div>
        <?php if (!empty($data['image'])): ?>
            <div class="split-media framed"><?= media_tag($data['image'], t($data['title'] ?? '')) ?></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($data['principles'])): ?>
        <div class="wrap">
            <ul class="principles">
                <?php foreach ($data['principles'] as $p): ?><li><?= e(t($p['name'])) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
