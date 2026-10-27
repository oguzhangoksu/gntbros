<?php $cta = Store::get('home')['cta'] ?? []; ?>
<section class="section cta">
    <div class="wrap cta-grid">
        <div>
            <h2><?= e(t($cta['title'] ?? '')) ?></h2>
            <p class="lead"><?= e(t($cta['text'] ?? '')) ?></p>
            <a class="btn btn-red" href="<?= e(url('contact')) ?>"><?= e(t($cta['button'] ?? '')) ?></a>
        </div>
    </div>
</section>
