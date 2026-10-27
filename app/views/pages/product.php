<?php /** @var array $data */
$p = $data['item'];
$cat = $data['category'];
$images = array_values(array_filter(array_column($p['images'] ?? [], 'image')));
$specs = array_filter($p['specs'] ?? [], fn($s) => t($s['label'] ?? '') !== '');
page_head(t($p['name']), [__('products') => url('products'), t($cat['name']) => url('products', $cat['slug']), t($p['name']) => null]);
?>
<section class="section">
    <div class="wrap product-detail">
        <div class="product-gallery">
            <?php if ($images): ?>
                <div class="product-main framed"><?= media_tag($images[0], t($p['name']), '', false) ?></div>
                <?php if (count($images) > 1): ?>
                    <div class="product-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <button type="button" data-src="<?= e(media($img)) ?>"<?= $i === 0 ? ' aria-pressed="true"' : '' ?>><?= media_tag($img, t($p['name']) . ' ' . ($i + 1)) ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="product-info prose">
            <p class="product-cat"><a href="<?= e(url('products', $cat['slug'])) ?>"><?= e(t($cat['name'])) ?></a></p>
            <?= paragraphs(t($p['description'] ?? '') ?: t($p['summary'] ?? '')) ?>

            <?php if ($specs): ?>
                <h2 class="h3"><?= e(__('specs')) ?></h2>
                <table class="specs">
                    <tbody>
                    <?php foreach ($specs as $s): ?>
                        <tr><th scope="row"><?= e(t($s['label'])) ?></th><td><?= e(t($s['value'] ?? '')) ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <a class="btn btn-red" href="<?= e(url('contact')) ?>?konu=<?= rawurlencode(t($p['name'])) ?>"><?= e(__('ask_product')) ?></a>
        </div>
    </div>
</section>

<?php if ($data['related']): ?>
<section class="section related">
    <div class="wrap">
        <h2 class="section-title"><?= e(__('related')) ?></h2>
        <ul class="product-grid">
            <?php foreach ($data['related'] as $r): ?>
                <li class="product">
                    <a href="<?= e(url('product', $r['slug'])) ?>">
                        <span class="product-img"><?= media_tag($r['images'][0]['image'] ?? '', t($r['name'])) ?></span>
                        <h3 class="product-name"><?= e(t($r['name'])) ?></h3>
                        <p><?= e(t($r['summary'] ?? '')) ?></p>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>
