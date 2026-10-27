<?php /** @var array $data @var array $settings */
$c = $settings['contact'] ?? [];
$flash = $data['flash'];
$old = $flash['old'] ?? [];
$val = fn(string $k) => e($old[$k] ?? ($k === 'subject' ? mb_substr((string) ($_GET['konu'] ?? ''), 0, 200) : ''));
page_head(__('contact'), [__('contact') => null]);
?>
<section class="section">
    <div class="wrap contact-grid">
        <div class="contact-info">
            <dl class="contact-list">
                <dt><?= e(__('address')) ?></dt>
                <dd>
                    <?= e($settings['site']['legal_name'] ?? '') ?><br>
                    <?= nl2br(e(t($c['address'] ?? '')), false) ?>
                </dd>
                <?php if (!empty($c['phones'])): ?>
                    <dt><?= e(__('phone')) ?></dt>
                    <dd><?php foreach ($c['phones'] as $p): ?><a href="<?= e(tel_href($p['number'])) ?>"><?= e($p['number']) ?></a><br><?php endforeach; ?></dd>
                <?php endif; ?>
                <?php if (!empty($c['email'])): ?>
                    <dt><?= e(__('email')) ?></dt>
                    <dd><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></dd>
                <?php endif; ?>
                <?php if (t($c['hours'] ?? '') !== ''): ?>
                    <dt><?= e(__('hours')) ?></dt>
                    <dd><?= nl2br(e(t($c['hours'])), false) ?></dd>
                <?php endif; ?>
            </dl>
            <?php if (!empty($c['map_embed'])): ?>
                <div class="map framed">
                    <iframe src="<?= e($c['map_embed']) ?>" title="<?= e(__('address')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
            <?php endif; ?>
        </div>

        <form class="contact-form" method="post" action="<?= e(url('contact')) ?>" novalidate>
            <h2><?= e(__('form_title')) ?></h2>
            <div class="form-status" role="status" aria-live="polite"<?= $flash ? '' : ' hidden' ?> data-state="<?= $flash ? ($flash['ok'] ? 'ok' : 'error') : '' ?>"><?= e($flash['message'] ?? '') ?></div>

            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="_ts" value="<?= time() ?>">
            <div class="hp" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

            <div class="field-row">
                <label class="field"><span><?= e(__('form_name')) ?></span><input type="text" name="name" required maxlength="120" autocomplete="name" value="<?= $val('name') ?>"></label>
                <label class="field"><span><?= e(__('form_company')) ?></span><input type="text" name="company" maxlength="160" autocomplete="organization" value="<?= $val('company') ?>"></label>
            </div>
            <div class="field-row">
                <label class="field"><span><?= e(__('form_email')) ?></span><input type="email" name="email" required maxlength="160" autocomplete="email" value="<?= $val('email') ?>"></label>
                <label class="field"><span><?= e(__('form_phone')) ?></span><input type="tel" name="phone" maxlength="40" autocomplete="tel" value="<?= $val('phone') ?>"></label>
            </div>
            <label class="field"><span><?= e(__('form_subject')) ?></span><input type="text" name="subject" required maxlength="200" value="<?= $val('subject') ?>"></label>
            <label class="field"><span><?= e(__('form_message')) ?></span><textarea name="message" rows="6" required maxlength="5000" placeholder="<?= e(__('form_message_ph')) ?>"><?= $val('message') ?></textarea></label>
            <label class="check"><input type="checkbox" name="consent" value="1" required> <span><?= e(__('form_kvkk')) ?></span></label>
            <button class="btn btn-red" type="submit" data-sending="<?= e(__('form_sending')) ?>"><?= e(__('form_send')) ?></button>
        </form>
    </div>
</section>
