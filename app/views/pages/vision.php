<?php /** @var array $data */ page_head(__('vision'), [__('corporate') => '', __('vision') => null]); ?>
<section class="section">
    <div class="wrap vm-grid">
        <article class="vm">
            <h2><?= e(t($data['vision_title'] ?? '')) ?></h2>
            <p class="lead"><?= e(t($data['vision_text'] ?? '')) ?></p>
        </article>
        <article class="vm">
            <h2><?= e(t($data['mission_title'] ?? '')) ?></h2>
            <p class="lead"><?= e(t($data['mission_text'] ?? '')) ?></p>
        </article>
    </div>
</section>

<?= broach_divider('thin') ?>

<section class="section">
    <div class="wrap split">
        <div class="split-copy prose">
            <?php foreach ($data['info'] ?? [] as $b): ?>
                <h2 class="h3"><?= e(t($b['title'])) ?></h2>
                <?= paragraphs(t($b['text'])) ?>
            <?php endforeach; ?>
            <?php if (!empty($data['uses'])): ?>
                <ul class="tick-list cols-2">
                    <?php foreach ($data['uses'] as $u): ?><li><?= e(t($u['name'])) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if (!empty($data['image'])): ?>
            <div class="split-media framed"><?= media_tag($data['image'], t($data['info'][0]['title'] ?? '')) ?></div>
        <?php endif; ?>
    </div>

    <?php if (!empty($data['features'])): ?>
        <div class="wrap features">
            <h2><?= e(t($data['features_title'] ?? '')) ?></h2>
            <div class="feature-cols">
                <?php foreach ($data['features'] as $f): ?>
                    <div class="feature">
                        <h3><?= e(t($f['title'])) ?></h3>
                        <ul class="tick-list">
                            <?php foreach (preg_split('/\R/u', t($f['points'])) ?: [] as $line): ?>
                                <?php if (trim($line) !== ''): ?><li><?= e(trim($line)) ?></li><?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
