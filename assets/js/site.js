/* GNT Broş — site etkileşimleri (jQuery) */
(function ($) {
    'use strict';


    // --- Mobil menü ---
    var $toggle = $('.nav-toggle'), $nav = $('#site-nav');
    $toggle.on('click', function () {
        var open = $toggle.attr('aria-expanded') !== 'true';
        $toggle.attr('aria-expanded', open);
        $nav.toggleClass('open', open);
        $('body').toggleClass('nav-open', open);
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $nav.hasClass('open')) { $toggle.trigger('click').trigger('focus'); }
    });

    // --- Alt menü (dokunmatik ve klavye) ---
    $('.sub-toggle').on('click', function () {
        var $li = $(this).parent(), open = !$li.hasClass('open');
        $('.has-sub').not($li).removeClass('open').children('.sub-toggle').attr('aria-expanded', false);
        $li.toggleClass('open', open);
        $(this).attr('aria-expanded', open);
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.has-sub').length) {
            $('.has-sub').removeClass('open').children('.sub-toggle').attr('aria-expanded', false);
        }
    });

    // --- İletişim formu (AJAX; JS yoksa normal POST çalışır) ---
    $('.contact-form').on('submit', function (e) {
        var form = this, $form = $(form), $btn = $form.find('[type=submit]'), $status = $form.find('.form-status');
        if (!form.checkValidity()) {
            e.preventDefault();
            $form.find(':invalid').attr('aria-invalid', 'true').first().trigger('focus');
            form.reportValidity && form.reportValidity();
            return;
        }
        e.preventDefault();
        var label = $btn.text();
        $btn.prop('disabled', true).text($btn.data('sending'));
        $.ajax({ url: $form.attr('action'), method: 'POST', data: $form.serialize(), dataType: 'json' })
            .done(function (r) {
                $status.attr('data-state', 'ok').text(r.message).prop('hidden', false);
                form.reset();
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Bir hata oluştu, lütfen tekrar deneyin.';
                $status.attr('data-state', 'error').text(msg).prop('hidden', false);
            })
            .always(function () {
                $btn.prop('disabled', false).text(label);
                $status[0].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            });
    }).on('input change', '[aria-invalid]', function () {
        if (this.checkValidity()) { $(this).removeAttr('aria-invalid'); }
    });

    // --- Ürün görsel küçük resimleri ---
    $('.product-thumbs').on('click', 'button', function () {
        var $b = $(this);
        $b.siblings().attr('aria-pressed', 'false');
        $b.attr('aria-pressed', 'true');
        $('.product-main img').attr('src', $b.data('src'));
    });

    // --- Galeri lightbox ---
    var $lb = $('.lightbox'), $links = $('.gallery-item a'), idx = 0, lastFocus = null;
    function show(i) {
        idx = (i + $links.length) % $links.length;
        var $a = $links.eq(idx);
        $lb.find('img').attr({ src: $a.attr('href'), alt: $a.find('img').attr('alt') });
        $lb.find('figcaption').text($a.data('caption') || '');
    }
    function close() {
        $lb.prop('hidden', true);
        $('body').css('overflow', '');
        lastFocus && lastFocus.focus();
    }
    $links.on('click', function (e) {
        e.preventDefault();
        lastFocus = this;
        show($links.index(this));
        $lb.prop('hidden', false);
        $('body').css('overflow', 'hidden');
        $lb.find('.lightbox-close').trigger('focus');
    });
    $lb.on('click', '.prev', function () { show(idx - 1); })
        .on('click', '.next', function () { show(idx + 1); })
        .on('click', '.lightbox-close', close)
        .on('click', function (e) { if (e.target === this) { close(); } });
    $(document).on('keydown', function (e) {
        if ($lb.length && !$lb.prop('hidden')) {
            if (e.key === 'Escape') { close(); }
            if (e.key === 'ArrowLeft') { show(idx - 1); }
            if (e.key === 'ArrowRight') { show(idx + 1); }
        }
    });
})(jQuery);
