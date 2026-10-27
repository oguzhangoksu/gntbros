<?php /** @var array $data */
$intro = $data['intro'] ?? [];
$current = $data['current'];
$crumbs = $current ? [__('products') => url('products'), t($current['name']) => null] : [__('products') => null];
page_head($current ? t($current['name']) : (t($intro['title'] ?? '') ?: __('products')), $crumbs, $current ? t($current['text'] ?? '') : t($intro['text'] ?? ''));
?>
<section class="section">
    <div class="wrap">
        <?php if (count($data['categories']) > 1): ?>
            <nav class="cat-filter" aria-label="<?= e(__('products')) ?>">
                <a href="<?= e(url('products')) ?>"<?= !$current ? ' aria-current="page"' : '' ?>><?= e(__('all')) ?></a>
                <?php foreach ($data['categories'] as $c): ?>
                    <a href="<?= e(url('products', $c['slug'])) ?>"<?= $current && $current['slug'] === $c['slug'] ? ' aria-current="page"' : '' ?>><?= e(t($c['name'])) ?></a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if (!$data['items']): ?>
            <div class="empty">
                <p><?= e(__('no_products')) ?></p>
                <a class="btn btn-red" href="<?= e(url('contact')) ?>"><?= e(__('request_quote')) ?></a>
            </div>
        <?php else: ?>
            <ul class="product-grid">
                <?php foreach ($data['items'] as $p): ?>
                    <li class="product">
                        <a href="<?= e(url('product', $p['slug'])) ?>">
                            <span class="product-img"><?= media_tag($p['images'][0]['image'] ?? '', t($p['name'])) ?></span>
                            <h2 class="product-name"><?= e(t($p['name'])) ?></h2>
                            <p><?= e(t($p['summary'] ?? '')) ?></p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php $pg = $data['pager']; if ($pg['pages'] > 1):
                $from = ($pg['page'] - 1) * $pg['per_page'] + 1;
                $to = min($pg['total'], $pg['page'] * $pg['per_page']); ?>
                <nav class="pager" aria-label="<?= e(__('pagination')) ?>">
                    <p class="pager-info"><?= e($from === $to ? sprintf(__('showing_one'), $from, $pg['total']) : sprintf(__('showing'), $from, $to, $pg['total'])) ?></p>
                    <ul>
                        <li>
                            <?php if ($pg['page'] > 1): ?>
                                <a class="pager-step" href="<?= e(products_url($pg['cat'], $pg['page'] - 1)) ?>" rel="prev"><?= e(__('prev_page')) ?></a>
                            <?php else: ?>
                                <span class="pager-step" aria-disabled="true"><?= e(__('prev_page')) ?></span>
                            <?php endif; ?>
                        </li>
                        <?php foreach (page_numbers($pg['page'], $pg['pages']) as $n): ?>
                            <li>
                                <?php if ($n === null): ?>
                                    <span class="pager-gap" aria-hidden="true">…</span>
                                <?php elseif ($n === $pg['page']): ?>
                                    <span class="pager-num" aria-current="page"><span class="sr-only"><?= e(__('page')) ?> </span><?= $n ?></span>
                                <?php else: ?>
                                    <a class="pager-num" href="<?= e(products_url($pg['cat'], $n)) ?>"><span class="sr-only"><?= e(__('page')) ?> </span><?= $n ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                        <li>
                            <?php if ($pg['page'] < $pg['pages']): ?>
                                <a class="pager-step" href="<?= e(products_url($pg['cat'], $pg['page'] + 1)) ?>" rel="next"><?= e(__('next_page')) ?></a>
                            <?php else: ?>
                                <span class="pager-step" aria-disabled="true"><?= e(__('next_page')) ?></span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
<?php require __DIR__ . '/_cta.php'; ?>
