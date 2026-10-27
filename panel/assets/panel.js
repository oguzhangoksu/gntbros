/* GNT Broş panel (jQuery) */
(function ($) {
    'use strict';

    var csrf = $('meta[name=csrf]').attr('content');
    $.ajaxSetup({ headers: { 'X-CSRF-Token': csrf } });

    var dirty = false;

    // ------------------------------------------------------------ Bildirim ve onay pencereleri (SweetAlert2)
    var COLORS = { confirm: '#c8161d', cancel: '#5a6a78', ink: '#23303b' };
    var Toast = Swal.mixin({
        toast: true, position: 'top-end', showConfirmButton: false, timer: 3500, timerProgressBar: true,
        didOpen: function (el) { el.addEventListener('mouseenter', Swal.stopTimer); el.addEventListener('mouseleave', Swal.resumeTimer); }
    });
    // Başarı ve uyarı köşede kısa bildirim; hata okunabilsin diye ortada pencere
    function toast(msg, type) {
        if (type === 'error') {
            return Swal.fire({ icon: 'error', title: 'İşlem tamamlanamadı', text: msg, confirmButtonText: 'Tamam', confirmButtonColor: COLORS.ink });
        }
        return Toast.fire({ icon: type === 'warn' ? 'warning' : 'success', title: msg, timer: type === 'warn' ? 6000 : 3500 });
    }
    // Silme gibi geri alınamaz işlemler için onay; Promise<boolean> döner
    function confirmDanger(title, text, button) {
        return Swal.fire({
            icon: 'warning', title: title, text: text, showCancelButton: true, focusCancel: true,
            confirmButtonText: button || 'Sil', cancelButtonText: 'Vazgeç',
            confirmButtonColor: COLORS.confirm, cancelButtonColor: COLORS.cancel, reverseButtons: true
        }).then(function (r) { return r.isConfirmed; });
    }
    function errMsg(xhr) {
        return (xhr.responseJSON && xhr.responseJSON.message) || 'İstek tamamlanamadı (' + xhr.status + ').';
    }

    // ------------------------------------------------------------ Form verisini topla
    // Her .scope (form, grup gövdesi, tekrar öğesi) kendi doğrudan .f alanlarını toplar.
    function directFields(scope) {
        return $(scope).find('.f').filter(function () {
            return $(this).parent().closest('.scope')[0] === scope;
        });
    }

    function collect(scope) {
        var out = {};
        directFields(scope).each(function () {
            var $f = $(this), key = $f.data('key'), type = $f.data('type');
            if (type === 'group') {
                out[key] = collect($f.children('.group-body')[0]);
            } else if (type === 'repeater') {
                out[key] = $f.children('.rep').children('.rep-items').children('.rep-item').map(function () {
                    return [collect($(this).children('.rep-body')[0])];
                }).get();
            } else if ($f.data('i18n')) {
                var v = {};
                $f.children('.ctl').find('.v').each(function () { v[$(this).data('lang')] = $(this).val(); });
                out[key] = v;
            } else if (type === 'checkbox') {
                out[key] = $f.find('.v').first().prop('checked');
            } else {
                out[key] = $f.children('.ctl').find('.v').first().val();
            }
        });
        return out;
    }

    // ------------------------------------------------------------ Kaydet
    function save() {
        var $form = $('#editor'), $btn = $('.save');
        if (!$form.length) { return; }
        $btn.prop('disabled', true).text('Kaydediliyor…');
        $.post('api.php?action=save&section=' + encodeURIComponent($form.data('section')), { data: JSON.stringify(collect($form[0])) }, null, 'json')
            .done(function (r) {
                dirty = false;
                if (r.warnings && r.warnings.length) {
                    Swal.fire({ icon: 'warning', title: r.message, html: $('<div>').append(r.warnings.map(function (w) { return $('<p style="margin:4px 0">').text(w); })).html(), confirmButtonText: 'Tamam', confirmButtonColor: COLORS.ink });
                } else {
                    toast(r.message, 'ok');
                }
                if (r.reload) { setTimeout(function () { location.reload(); }, 900); }
            })
            .fail(function (xhr) { toast(errMsg(xhr), 'error'); })
            .always(function () { $btn.prop('disabled', false).text('Kaydet'); });
    }
    $(document).on('click', '.save', save);
    $(document).on('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); save(); }
    });
    $('#editor').on('input change', function () { dirty = true; });
    $(window).on('beforeunload', function () { if (dirty) { return 'Kaydedilmemiş değişiklikler var.'; } });

    // ------------------------------------------------------------ Tekrarlayıcılar
    function refreshRepeater($f) {
        var $items = $f.children('.rep').children('.rep-items').children('.rep-item');
        $f.find('> .rep-header .count').text('(' + $items.length + ')');
        $items.each(function (i) { updateTitle($(this), i); });
    }
    function updateTitle($item, i) {
        var key = $item.data('label-key'), text = '';
        if (key) {
            var $field = $item.children('.rep-body').children('.f[data-key="' + key + '"]');
            text = $field.find('.v').first().val() || '';
        }
        if (!text) {
            var img = $item.children('.rep-body').find('.f-image .v, .f-media .v').first().val();
            text = img ? img.replace('uploads/', '') : '';
        }
        $item.find('> .rep-head .rep-title').text((i + 1) + '. ' + (text || 'Yeni kayıt'));
    }
    $('.f-repeater').each(function () { refreshRepeater($(this)); });

    $(document).on('click', '.rep-add', function () {
        var $f = $(this).closest('.f-repeater'), tpl = $f.children('.rep').children('template')[0];
        var $item = $(document.importNode(tpl.content, true)).children('.rep-item');
        $f.children('.rep').children('.rep-items').append($item);
        refreshRepeater($f);
        dirty = true;
        $item.find('input, textarea').filter(':visible').first().trigger('focus');
    });
    $(document).on('click', '.rep-tools button', function () {
        var $b = $(this), $item = $b.closest('.rep-item'), $f = $item.closest('.f-repeater');
        if ($b.hasClass('del')) {
            var name = $.trim($item.find('> .rep-head .rep-title').text()).replace(/^\d+\.\s*/, '');
            confirmDanger('Kayıt silinsin mi?', '“' + name + '” listeden çıkarılacak. Kaydet’e basana kadar sitede bir şey değişmez.').then(function (yes) {
                if (!yes) { return; }
                $item.remove();
                refreshRepeater($f);
                dirty = true;
            });
            return;
        } else if ($b.hasClass('up')) {
            $item.prev('.rep-item').before($item);
        } else if ($b.hasClass('down')) {
            $item.next('.rep-item').after($item);
        }
        refreshRepeater($f);
        dirty = true;
    });
    $(document).on('click', '.rep-toggle, .rep-title', function () {
        $(this).closest('.rep-item').toggleClass('collapsed');
    });
    $(document).on('input', '.rep-body .v', function () {
        var $item = $(this).closest('.rep-item');
        updateTitle($item, $item.index());
    });

    // Slug alanı boşken kaynak alana göre öneri göster
    function slugify(s) {
        var map = { 'ç': 'c', 'ğ': 'g', 'ı': 'i', 'i̇': 'i', 'ö': 'o', 'ş': 's', 'ü': 'u', 'â': 'a', 'î': 'i', 'û': 'u' };
        return s.toLocaleLowerCase('tr').replace(/[çğıöşüâîû]/g, function (c) { return map[c] || c; })
            .replace(/&/g, ' ve ').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }
    $(document).on('input', '.rep-body .v[data-lang="tr"]', function () {
        var $f = $(this).closest('.f'), key = $f.data('key');
        $(this).closest('.rep-body').children('.f-slug').each(function () {
            var $v = $(this).find('.v');
            if ($v.data('from') === key) { $v.attr('placeholder', slugify($(this).closest('.rep-body').children('.f[data-key="' + key + '"]').find('.v').first().val())); }
        });
    });

    // ------------------------------------------------------------ Medya alanları
    // Yükleme / kütüphane / kaldır: değeri yazar ve önizlemeyi yeniler.
    // 'change' tetiklenmez; o olay elle yazılan yol içindir ve burada tetiklenirse sonsuz döngüye girer.
    function setMedia($f, path) {
        $f.find('.v').first().val(path).trigger('input');
        renderMedia($f, path);
    }
    function renderMedia($f, path) {
        var $p = $f.find('.preview').empty();
        var $name = $f.find('.file-name');
        if (!path) {
            $p.html('<span class="empty-prev">Dosya yok</span>');
            $name.html('<span class="muted">Dosya seçilmedi</span>');
            return;
        }
        var url = BASE + '/' + path;
        var video = /\.(mp4|webm)$/i.test(path);
        $p.append(video ? $('<video muted preload="metadata">').attr('src', url) : $('<img alt="">').attr('src', url));
        $p.append($('<span class="badge-kind">').text(video ? 'Video' : 'Görsel'));
        $name.text(path.replace(/^uploads\//, ''));
        var $item = $f.closest('.rep-item');
        if ($item.length) { updateTitle($item, $item.index()); }
    }
    var BASE = (function () {
        var p = location.pathname; return p.substring(0, p.indexOf('/panel'));
    })();

    function upload(file, done) {
        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', csrf);
        return $.ajax({ url: 'api.php?action=upload', method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
            .done(done)
            .fail(function (xhr) { toast(file.name + ': ' + errMsg(xhr), 'error'); });
    }

    $(document).on('change', '.media-card .upload', function () {
        var $f = $(this).closest('.f'), file = this.files[0], $btn = $(this).parent();
        if (!file) { return; }
        $btn.addClass('busy');
        upload(file, function (r) { setMedia($f, r.path); toast('Dosya yüklendi. Kaydet’e basmayı unutmayın.', 'ok'); })
            .always(function () { $btn.removeClass('busy'); });
        this.value = '';
    });
    $(document).on('click', '.media-card .clear', function () { setMedia($(this).closest('.f'), ''); });
    $(document).on('change', '.media-card .v', function () {
        // Elle yazılan yol için yalnızca önizlemeyi güncelle
        renderMedia($(this).closest('.f'), $.trim($(this).val()));
    });

    // Kütüphane penceresi
    var pickTarget = null, $modal = $('#library');
    function openLibrary($f, kind) {
        pickTarget = $f;
        var $grid = $modal.find('.picker').html('<p class="muted">Yükleniyor…</p>');
        $modal.prop('hidden', false);
        $.post('api.php?action=library', {}, null, 'json').done(function (r) {
            $grid.empty();
            var files = r.files.filter(function (x) { return kind === 'media' || (kind === 'video') === x.video; });
            if (!files.length) { $grid.html('<p class="muted">Uygun dosya yok. Önce bilgisayardan yükleyin.</p>'); }
            files.forEach(function (x) {
                var $b = $('<button type="button" class="lib-item">').attr('data-path', x.path);
                var $t = $('<span class="lib-thumb">').append(x.video ? $('<video muted preload="metadata">').attr('src', x.url) : $('<img alt="" loading="lazy">').attr('src', x.url));
                $b.append($t, $('<span class="lib-name">').text(x.path.replace('uploads/', '')));
                $grid.append($b);
            });
            $grid.find('button').first().trigger('focus');
        }).fail(function (xhr) { $grid.html('').append($('<p>').text(errMsg(xhr))); });
    }
    function closeLibrary() { $modal.prop('hidden', true); pickTarget = null; }
    $(document).on('click', '.media-card .pick', function () { openLibrary($(this).closest('.f'), $(this).data('kind')); });
    $modal.on('click', '.picker .lib-item', function () { if (pickTarget) { setMedia(pickTarget, $(this).data('path')); } closeLibrary(); });
    $modal.on('click', '.modal-close', closeLibrary).on('click', function (e) { if (e.target === this) { closeLibrary(); } });
    $(document).on('keydown', function (e) { if (e.key === 'Escape' && !$modal.prop('hidden')) { closeLibrary(); } });

    // Medya sayfası
    // Toplu yükleme: tek tek hata penceresi açmak yerine sonda tek bir özet gösterilir
    $('.lib-upload').on('change', function () {
        var files = Array.prototype.slice.call(this.files), left = files.length, ok = 0, failed = [];
        if (!left) { return; }
        Swal.fire({ title: 'Yükleniyor…', text: left + ' dosya', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        files.forEach(function (file) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('_token', csrf);
            $.ajax({ url: 'api.php?action=upload', method: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
                .done(function () { ok++; })
                .fail(function (xhr) { failed.push(file.name + ': ' + errMsg(xhr)); })
                .always(function () {
                    if (--left > 0) { return; }
                    var opts = failed.length
                        ? { icon: ok ? 'warning' : 'error', title: ok + ' dosya yüklendi, ' + failed.length + ' dosya yüklenemedi', html: $('<div>').append(failed.map(function (f) { return $('<p style="margin:4px 0;text-align:left">').text(f); })).html() }
                        : { icon: 'success', title: ok + ' dosya yüklendi', timer: 1500, showConfirmButton: false };
                    Swal.fire($.extend({ confirmButtonText: 'Tamam', confirmButtonColor: COLORS.ink }, opts)).then(function () { location.reload(); });
                });
        });
        this.value = '';
    });
    $('.media-page').on('click', '.copy-path', function () {
        var path = $(this).closest('.lib-item').data('path');
        (navigator.clipboard ? navigator.clipboard.writeText(path) : $.Deferred().reject()).then(
            function () { toast('Kopyalandı: ' + path, 'ok'); },
            function () {
                // Panoya erişim yoksa (ör. http üzerinde) yolu seçili halde göster
                Swal.fire({ title: 'Dosya yolu', input: 'text', inputValue: path, inputAttributes: { readonly: 'readonly' },
                    text: 'Kopyalamak için Ctrl+C’ye basın.', confirmButtonText: 'Kapat', confirmButtonColor: COLORS.ink,
                    didOpen: function () { Swal.getInput().select(); } });
            }
        );
    }).on('click', '.del-file', function () {
        var $item = $(this).closest('.lib-item'), path = $item.data('path');
        confirmDanger('Dosya silinsin mi?', path.replace('uploads/', '') + ' kalıcı olarak silinecek. Bu işlem geri alınamaz.').then(function (yes) {
            if (!yes) { return; }
            $.post('api.php?action=delete_file', { path: path }, null, 'json')
                .done(function (r) { $item.remove(); toast(r.message, 'ok'); })
                .fail(function (xhr) { toast(errMsg(xhr), 'error'); });
        });
    });

    // ------------------------------------------------------------ Mesajlar
    function messageOp($m, op) {
        $.post('api.php?action=message', { id: $m.data('id'), op: op }, null, 'json').done(function (r) {
            if (op === 'delete') { $m.remove(); toast('Mesaj silindi.', 'ok'); } else {
                $m.toggleClass('unread', op === 'unread');
                $m.find('.msg-read').text(op === 'unread' ? 'Okundu işaretle' : 'Okunmadı işaretle');
            }
            $('[data-unread]').text(r.unread).prop('hidden', !r.unread);
        }).fail(function (xhr) { toast(errMsg(xhr), 'error'); });
    }
    $('.messages').on('click', '.msg-read', function () {
        var $m = $(this).closest('.msg');
        messageOp($m, $m.hasClass('unread') ? 'read' : 'unread');
    }).on('click', '.msg-del', function () {
        var $m = $(this).closest('.msg'), from = $.trim($m.find('.msg-head strong').first().text());
        confirmDanger('Mesaj silinsin mi?', from + ' kişisinden gelen mesaj kalıcı olarak silinecek.').then(function (yes) {
            if (yes) { messageOp($m, 'delete'); }
        });
    });

    // ------------------------------------------------------------ SMTP denemesi
    $('.test-mail').on('click', function () {
        if (dirty) { toast('Önce ayarları kaydedin, sonra deneme e-postası gönderin.', 'warn'); return; }
        var $b = $(this).prop('disabled', true), $log = $('.mail-log').prop('hidden', true);
        Swal.fire({ title: 'Gönderiliyor…', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });
        $.post('api.php?action=test_mail', {}, null, 'json')
            .done(function (r) { Swal.fire({ icon: 'success', title: 'Deneme e-postası gönderildi', text: r.message, confirmButtonText: 'Tamam', confirmButtonColor: COLORS.ink }); })
            .fail(function (xhr) {
                toast(errMsg(xhr), 'error');
                if (xhr.responseJSON && xhr.responseJSON.log) { $log.text(xhr.responseJSON.log).prop('hidden', false); }
            })
            .always(function () { $b.prop('disabled', false); });
    });

    // ------------------------------------------------------------ Bölüm sekmeleri
    var $tabs = $('.ed-tab');
    function showTab($tab) {
        $tabs.attr('aria-selected', 'false');
        $tab.attr('aria-selected', 'true');
        $('.ed-pane').prop('hidden', true);
        $('#' + $tab.attr('aria-controls')).prop('hidden', false);
        try { sessionStorage.setItem('gnt-tab-' + $('#editor').data('section'), $tab.attr('id')); } catch (e) { /* yok say */ }
    }
    $tabs.on('click', function () { showTab($(this)); });
    $('.ed-tabs').on('keydown', '.ed-tab', function (e) {
        var i = $tabs.index(this), n = null;
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') { n = (i + 1) % $tabs.length; }
        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') { n = (i - 1 + $tabs.length) % $tabs.length; }
        if (n !== null) { e.preventDefault(); showTab($tabs.eq(n)); $tabs.eq(n).trigger('focus'); }
    });
    // Açılışta: adresteki #tab-N, yoksa bu bölümde en son açık kalan sekme
    var initialTab = /^#tab-\d+$/.test(location.hash) ? location.hash.slice(1) : null;
    try { initialTab = initialTab || sessionStorage.getItem('gnt-tab-' + $('#editor').data('section')); } catch (e) { /* yok say */ }
    if (initialTab && $('#' + initialTab).length) { showTab($('#' + initialTab)); }

    // SMTP deneme kartını kendi sekmesine taşı
    $('[data-move-to]').each(function () {
        var $target = $('.f[data-key="' + $(this).data('move-to') + '"]').closest('.ed-pane');
        if ($target.length) { $target.append(this); }
    });

    // ------------------------------------------------------------ Düzenlenen dil (TR / EN)
    // Her iki dilin kutuları sayfada durur; yalnızca seçili dil görünür.
    function syncPlaceholders() {
        $('.f[data-i18n] > .ctl').each(function () {
            var tr = $(this).find('.v[data-lang="tr"]').val() || '';
            $(this).find('.v[data-lang="en"]').attr('placeholder', tr ? tr : 'Türkçesi de boş');
        });
    }
    function countMissing() {
        // Türkçesi dolu ama İngilizcesi boş alanlar, sekme bazında
        var total = 0;
        $('.ed-pane').each(function () {
            var n = $(this).find('.f[data-i18n] > .ctl').filter(function () {
                return $.trim($(this).find('.v[data-lang="tr"]').val()) !== '' && $.trim($(this).find('.v[data-lang="en"]').val()) === '';
            }).length;
            total += n;
            $('#' + $(this).attr('aria-labelledby')).find('.ed-tab-missing').text(n).prop('hidden', !n || editLang !== 'en')
                .attr('title', n + ' alanın İngilizcesi boş');
        });
        $('.lang-switch .missing').text(total + ' boş').prop('hidden', !total);
    }
    var editLang = 'tr';
    function setLang(l) {
        editLang = l;
        $('body').attr('data-edit-lang', l);
        $('.lang-switch [data-lang]').each(function () { $(this).attr('aria-checked', $(this).data('lang') === l); });
        $('.lang-hint').prop('hidden', l !== 'en');
        $('.f[data-i18n] > .lbl').each(function () { $(this).attr('for', $(this).data('for') + '-' + l); });
        countMissing();
        try { localStorage.setItem('gnt-edit-lang', l); } catch (e) { /* yok say */ }
    }
    $('.lang-switch').on('click', '[data-lang]', function () { setLang($(this).data('lang')); });
    $('#editor').on('input', '.f[data-i18n] .v', function () {
        if ($(this).data('lang') === 'tr') { syncPlaceholders(); }
        countMissing();
    });
    if ($('#editor').length) {
        syncPlaceholders();
        var savedLang = 'tr';
        try { savedLang = localStorage.getItem('gnt-edit-lang') || 'tr'; } catch (e) { /* yok say */ }
        setLang(savedLang === 'en' ? 'en' : 'tr');
    }

    // Yeni eklenen kayıtlarda da yer tutucuları güncelle
    $(document).on('click', '.rep-add', function () { setTimeout(function () { syncPlaceholders(); countMissing(); }, 0); });

    // ------------------------------------------------------------ Mobil menü
    $('.sb-toggle').on('click', function () {
        var open = $(this).attr('aria-expanded') !== 'true';
        $(this).attr('aria-expanded', open);
        $('.sidebar').toggleClass('open', open);
    });
})(jQuery);
