<?php /** @var array $data */
$intro = $data['intro'] ?? [];
$items = active_items($data['items'] ?? []);
$refs = active_items($data['references'] ?? []);
page_head(t($intro['title'] ?? '') ?: __('gallery'), [__('gallery') => null], t($intro['text'] ?? ''));
?>
<section class="section">
    <div class="wrap">
        <div class="gallery">
            <?php foreach ($items as $g): ?>
                <figure class="gallery-item">
                    <a href="<?= e(media($g['image'])) ?>" data-caption="<?= e(t($g['caption'] ?? '')) ?>">
                        <?= media_tag($g['image'], t($g['caption'] ?? '') ?: __('gallery')) ?>
                    </a>
                    <?php if (t($g['caption'] ?? '') !== ''): ?><figcaption><?= e(t($g['caption'])) ?></figcaption><?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($refs): ?>
<section class="section refs">
    <div class="wrap">
        <h2 class="section-title"><?= e(t($data['references_title'] ?? '') ?: __('references')) ?></h2>
        <ul class="ref-grid">
            <?php foreach ($refs as $r):
                $inner = !empty($r['logo']) ? media_tag($r['logo'], $r['name'] ?? '') : '<span>' . e($r['name'] ?? '') . '</span>'; ?>
                <li>
                    <?php if (!empty($r['url'])): ?>
                        <a href="<?= e($r['url']) ?>" target="_blank" rel="noopener" title="<?= e($r['name'] ?? '') ?>"><?= $inner ?></a>
                    <?php else: ?>
                        <?= $inner ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<div class="lightbox" hidden role="dialog" aria-modal="true">
    <button type="button" class="lightbox-close" aria-label="<?= e(__('close')) ?>">&times;</button>
    <button type="button" class="lightbox-nav prev" aria-label="<?= lang() === 'en' ? 'Previous' : 'Önceki' ?>">&lsaquo;</button>
    <figure><img alt=""><figcaption></figcaption></figure>
    <button type="button" class="lightbox-nav next" aria-label="<?= lang() === 'en' ? 'Next' : 'Sonraki' ?>">&rsaquo;</button>
</div>
