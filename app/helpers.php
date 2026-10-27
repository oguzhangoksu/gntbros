<?php
declare(strict_types=1);

const LANGS = ['tr', 'en'];

/** Sayfa anahtarı => dile göre URL yolu */
const ROUTES = [
    'home'         => ['tr' => '',                    'en' => 'en'],
    'about'        => ['tr' => 'hakkimizda',          'en' => 'en/about'],
    'vision'       => ['tr' => 'vizyon-misyon',       'en' => 'en/vision-mission'],
    'policy'       => ['tr' => 'kurulus-politikamiz', 'en' => 'en/our-policy'],
    'applications' => ['tr' => 'uygulamalarimiz',     'en' => 'en/applications'],
    'products'     => ['tr' => 'urunlerimiz',         'en' => 'en/products'],
    'product'      => ['tr' => 'urun',                'en' => 'en/product'],
    'gallery'      => ['tr' => 'galeri',              'en' => 'en/gallery'],
    'contact'      => ['tr' => 'iletisim',            'en' => 'en/contact'],
];

/** Arayüz metinleri (içerik değil; panelden yönetilmez) */
const UI = [
    'home'            => ['tr' => 'Anasayfa',              'en' => 'Home'],
    'corporate'       => ['tr' => 'Kurumsal',              'en' => 'Company'],
    'about'           => ['tr' => 'Hakkımızda',            'en' => 'About us'],
    'vision'          => ['tr' => 'Vizyon & Misyon',       'en' => 'Vision & Mission'],
    'policy'          => ['tr' => 'Kuruluş Politikamız',   'en' => 'Our Policy'],
    'applications'    => ['tr' => 'Uygulamalarımız',       'en' => 'Applications'],
    'products'        => ['tr' => 'Ürünlerimiz',           'en' => 'Products'],
    'gallery'         => ['tr' => 'Galeri',                'en' => 'Gallery'],
    'contact'         => ['tr' => 'İletişim',              'en' => 'Contact'],
    'all'             => ['tr' => 'Tümü',                  'en' => 'All'],
    'page'            => ['tr' => 'Sayfa',                 'en' => 'Page'],
    'prev_page'       => ['tr' => 'Önceki',                'en' => 'Previous'],
    'next_page'       => ['tr' => 'Sonraki',               'en' => 'Next'],
    'pagination'      => ['tr' => 'Sayfalar',              'en' => 'Pages'],
    'showing_one'     => ['tr' => '%2$d üründen %1$d. gösteriliyor', 'en' => 'Showing %1$d of %2$d products'],
    'showing'         => ['tr' => '%3$d üründen %1$d–%2$d arası gösteriliyor', 'en' => 'Showing %1$d–%2$d of %3$d products'],
    'menu'            => ['tr' => 'Menü',                  'en' => 'Menu'],
    'close'           => ['tr' => 'Kapat',                 'en' => 'Close'],
    'call'            => ['tr' => 'Ara',                   'en' => 'Call'],
    'request_quote'   => ['tr' => 'Teklif isteyin',        'en' => 'Request a quote'],
    'view_product'    => ['tr' => 'Ürünü incele',          'en' => 'View product'],
    'specs'           => ['tr' => 'Teknik bilgiler',       'en' => 'Specifications'],
    'related'         => ['tr' => 'Aynı kategorideki ürünler', 'en' => 'More in this category'],
    'no_products'     => ['tr' => 'Bu kategoride henüz ürün yok. Ürün bilgisi için bize yazın.', 'en' => 'No products in this category yet. Write to us for product details.'],
    'ask_product'     => ['tr' => 'Bu ürün için teklif isteyin', 'en' => 'Request a quote for this product'],
    'address'         => ['tr' => 'Adres',                 'en' => 'Address'],
    'phone'           => ['tr' => 'Telefon',               'en' => 'Phone'],
    'email'           => ['tr' => 'E-posta',               'en' => 'Email'],
    'hours'           => ['tr' => 'Çalışma saatleri',      'en' => 'Working hours'],
    'form_title'      => ['tr' => 'Bize yazın',            'en' => 'Write to us'],
    'form_name'       => ['tr' => 'Ad soyad',              'en' => 'Full name'],
    'form_company'    => ['tr' => 'Firma (isteğe bağlı)',  'en' => 'Company (optional)'],
    'form_email'      => ['tr' => 'E-posta',               'en' => 'Email'],
    'form_phone'      => ['tr' => 'Telefon (isteğe bağlı)', 'en' => 'Phone (optional)'],
    'form_subject'    => ['tr' => 'Konu',                  'en' => 'Subject'],
    'form_message'    => ['tr' => 'Mesajınız',             'en' => 'Your message'],
    'form_message_ph' => ['tr' => 'Parça, malzeme, ölçü ve adet bilgisini yazarsanız daha hızlı dönüş yaparız.', 'en' => 'Include part, material, dimensions and quantity so we can reply faster.'],
    'form_kvkk'       => ['tr' => 'Bilgilerimin bu talebe dönüş yapmak için kullanılmasını kabul ediyorum.', 'en' => 'I agree that my details are used to reply to this request.'],
    'form_send'       => ['tr' => 'Mesajı gönder',         'en' => 'Send message'],
    'form_sending'    => ['tr' => 'Gönderiliyor…',         'en' => 'Sending…'],
    'form_ok'         => ['tr' => 'Mesajınız gönderildi. En kısa sürede dönüş yapacağız.', 'en' => 'Your message has been sent. We will get back to you shortly.'],
    'err_required'    => ['tr' => 'Ad, e-posta, konu ve mesaj alanlarını doldurun.', 'en' => 'Fill in name, email, subject and message.'],
    'err_email'       => ['tr' => 'E-posta adresi geçerli görünmüyor. Kontrol edip tekrar deneyin.', 'en' => 'The email address does not look valid. Check it and try again.'],
    'err_kvkk'        => ['tr' => 'Göndermek için onay kutusunu işaretleyin.', 'en' => 'Tick the consent box to send.'],
    'err_rate'        => ['tr' => 'Kısa sürede çok fazla mesaj gönderildi. Birkaç dakika sonra tekrar deneyin.', 'en' => 'Too many messages in a short time. Try again in a few minutes.'],
    'err_token'       => ['tr' => 'Formun süresi doldu. Sayfayı yenileyip tekrar gönderin.', 'en' => 'The form has expired. Reload the page and send again.'],
    'not_found'       => ['tr' => 'Aradığınız sayfa bulunamadı', 'en' => 'Page not found'],
    'not_found_text'  => ['tr' => 'Adres değişmiş ya da kaldırılmış olabilir. Anasayfadan devam edebilirsiniz.', 'en' => 'The address may have changed or been removed. Continue from the home page.'],
    'back_home'       => ['tr' => 'Anasayfaya dön',        'en' => 'Back to home'],
    'sectors'         => ['tr' => 'Çalıştığımız sektörler', 'en' => 'Industries we serve'],
    'references'      => ['tr' => 'Referanslarımız',       'en' => 'References'],
    'capacity'        => ['tr' => 'Kapasite',              'en' => 'Capacity'],
    'rights'          => ['tr' => 'Tüm hakları saklıdır.', 'en' => 'All rights reserved.'],
    'menu_label'      => ['tr' => 'Sayfalar',              'en' => 'Pages'],
    'follow'          => ['tr' => 'Bizi takip edin',       'en' => 'Follow us'],
];

$GLOBALS['lang'] = 'tr';

function lang(): string
{
    return $GLOBALS['lang'];
}

function set_lang(string $l): void
{
    $GLOBALS['lang'] = in_array($l, LANGS, true) ? $l : 'tr';
}

function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Çok dilli alanı aktif dile göre döndürür; boşsa Türkçeye düşer. */
function t(mixed $field): string
{
    if (is_array($field)) {
        $v = trim((string) ($field[lang()] ?? ''));
        return $v !== '' ? $v : (string) ($field['tr'] ?? '');
    }
    return (string) $field;
}

function __(string $key): string
{
    return UI[$key][lang()] ?? $key;
}

/** Düz metni paragraflara böler; HTML'e izin vermez. Tek satır sonu <br> olur. */
function paragraphs(string $text, string $class = ''): string
{
    $out = '';
    foreach (preg_split('/\R{2,}/u', trim($text)) ?: [] as $p) {
        if (trim($p) === '') {
            continue;
        }
        $out .= '<p' . ($class ? ' class="' . e($class) . '"' : '') . '>' . nl2br(e(trim($p)), false) . '</p>';
    }
    return $out;
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

/** Sitenin kök klasörü (alt klasörde çalışırken ör. /gntbros.com.tr). */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $docRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $root = str_replace('\\', '/', (string) realpath(ROOT));
        $base = ($docRoot !== '' && stripos($root, $docRoot) === 0) ? substr($root, strlen($docRoot)) : '';
        $base = rtrim($base, '/');
    }
    return $base;
}

function site_url(string $path = ''): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return (is_https() ? 'https://' : 'http://') . $host . base_path() . '/' . ltrim($path, '/');
}

/** Sayfa anahtarından URL üretir: url('product', 'mil-ve-saft') */
function url(string $page, string $param = '', ?string $l = null): string
{
    $l ??= lang();
    $path = ROUTES[$page][$l] ?? '';
    if ($param !== '') {
        $path .= '/' . rawurlencode($param);
    }
    return base_path() . '/' . $path;
}

/** Sayfa numarası segmenti: /urunlerimiz/sayfa/2, /en/products/page/2 */
const PAGE_WORD = ['tr' => 'sayfa', 'en' => 'page'];

/** Ürün listesi adresi (isteğe bağlı kategori ve sayfa numarasıyla) */
function products_url(string $cat = '', int $page = 1, ?string $l = null): string
{
    $l ??= lang();
    $u = url('products', $cat, $l);
    return $page > 1 ? $u . '/' . PAGE_WORD[$l] . '/' . $page : $u;
}

/**
 * Sayfalama bağlantıları için numara listesi; araya giren boşluklar null.
 * Örn. 12 sayfa, aktif 6: [1, null, 5, 6, 7, null, 12]
 */
function page_numbers(int $current, int $total): array
{
    if ($total <= 7) {
        return range(1, $total);
    }
    $keep = array_unique([1, 2, $current - 1, $current, $current + 1, $total - 1, $total]);
    $keep = array_filter($keep, fn($n) => $n >= 1 && $n <= $total);
    sort($keep);
    $out = [];
    $prev = 0;
    foreach ($keep as $n) {
        if ($n - $prev > 1) {
            $out[] = null;
        }
        $out[] = $n;
        $prev = $n;
    }
    return $out;
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . $path;
    $v = is_file($file) ? '?v=' . filemtime($file) : '';
    return base_path() . '/assets/' . $path . $v;
}

/** uploads/... yolunu veya tam URL'yi tarayıcı adresine çevirir. */
function media(string $path): string
{
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return base_path() . '/' . ltrim($path, '/');
}

function is_video(string $path): bool
{
    return (bool) preg_match('/\.(mp4|webm)$/i', $path);
}

function slugify(string $s): string
{
    $s = strtr(mb_strtolower(trim($s)), [
        'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'i̇' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
        'â' => 'a', 'î' => 'i', 'û' => 'u', '&' => ' ve ',
    ]);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
    return trim($s, '-');
}

function settings(): array
{
    return Store::get('settings');
}

function tel_href(string $phone): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    start_session();
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Yayında olan öğeleri paneldeki sırasıyla döndürür */
function active_items(array $items): array
{
    return array_values(array_filter($items, fn($i) => !empty($i['active'])));
}
