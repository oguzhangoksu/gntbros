<?php /** @var array $data */ page_head(__('about'), [__('corporate') => '', __('about') => null]); ?>
<section class="section">
    <div class="wrap split">
        <div class="split-copy prose">
            <h2><?= e(t($data['title'] ?? '')) ?></h2>
            <?php if (t($data['lead'] ?? '') !== ''): ?><p class="lead"><?= e(t($data['lead'])) ?></p><?php endif; ?>
            <?= paragraphs(t($data['text'] ?? '')) ?>
        </div>
        <?php if (!empty($data['image'])): ?>
            <div class="split-media framed"><?= media_tag($data['image'], t($data['title'] ?? '')) ?></div>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
