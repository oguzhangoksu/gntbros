<?php
/** @var array $settings */
$site = $settings['site'] ?? [];
$contact = $settings['contact'] ?? [];
$social = array_filter($settings['social'] ?? []);
$wa = preg_replace('/\D/', '', (string) ($contact['whatsapp'] ?? ''));
?>
</main>

<footer class="site-footer">
    <?= broach_divider('on-footer') ?>
    <div class="wrap footer-grid">
        <div class="footer-brand">
            <a class="brand brand-light" href="<?= e(url('home')) ?>"><?= brand_mark() ?></a>
            <?= paragraphs(t($site['footer_text'] ?? '')) ?>
            <p class="legal"><?= e($site['legal_name'] ?? '') ?></p>
        </div>

        <div>
            <h2 class="footer-title"><?= e(__('menu_label')) ?></h2>
            <ul class="footer-links">
                <li><a href="<?= e(url('about')) ?>"><?= e(__('about')) ?></a></li>
                <li><a href="<?= e(url('applications')) ?>"><?= e(__('applications')) ?></a></li>
                <li><a href="<?= e(url('products')) ?>"><?= e(__('products')) ?></a></li>
                <li><a href="<?= e(url('gallery')) ?>"><?= e(__('gallery')) ?></a></li>
                <li><a href="<?= e(url('contact')) ?>"><?= e(__('contact')) ?></a></li>
            </ul>
        </div>

        <div>
            <h2 class="footer-title"><?= e(__('contact')) ?></h2>
            <address class="footer-contact">
                <?= nl2br(e(t($contact['address'] ?? '')), false) ?><br>
                <?php foreach ($contact['phones'] ?? [] as $p): ?>
                    <a href="<?= e(tel_href($p['number'])) ?>"><?= e($p['number']) ?></a><br>
                <?php endforeach; ?>
                <?php if (!empty($contact['email'])): ?><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a><?php endif; ?>
            </address>
            <?php if ($social): ?>
                <ul class="social" aria-label="<?= e(__('follow')) ?>">
                    <?php foreach ($social as $name => $href): ?>
                        <li><a href="<?= e($href) ?>" target="_blank" rel="noopener"><?= e(ucfirst($name)) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="wrap footer-bottom">
        <span>© <?= date('Y') ?> <?= e($site['name'] ?? '') ?>. <?= e(__('rights')) ?></span>
        <a href="https://www.mydata.com.tr/" target="_blank" rel="noopener">Mydata Bilişim</a>
    </div>
</footer>

<?php if ($wa !== ''): ?>
    <a class="wa-float" href="https://wa.me/<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.3a.5.5 0 0 0 0-.4l-.8-1.9c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.7-1.2 2.2 2.2 0 0 0 .2-1.2c-.1-.1-.3-.2-.5-.3z"/></svg>
    </a>
<?php endif; ?>

<script src="<?= e(asset('js/jquery-3.6.2.min.js')) ?>"></script>
<script src="<?= e(asset('js/site.js')) ?>"></script>
</body>
</html>
