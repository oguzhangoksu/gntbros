<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
require __DIR__ . '/schemas.php';

const SESSION_TTL = 7200;        // 2 saat hareketsizlikte oturum kapanır
const MAX_LOGIN_FAILS = 10;      // 24 saat içinde bu kadar hatalı girişte IP banlanır
const FAIL_WINDOW = 86400;       // hatalı denemelerin sayıldığı süre (24 saat)
const BAN_SECONDS = 600;         // ban süresi (10 dakika)
const UPLOAD_TYPES = [
    'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
    'image/avif' => 'avif', 'image/x-icon' => 'ico', 'image/vnd.microsoft.icon' => 'ico',
    'video/mp4' => 'mp4', 'video/webm' => 'webm',
];
const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'ico'];
const VIDEO_EXT = ['mp4', 'webm'];

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

// Banlı IP, ban süresi dolana kadar panelin hiçbir sayfasına erişemez.
// Banı erken kaldırmak için: data/security.json > "banned" içinden IP satırını silin.
$banLeft = ban_seconds_left();
if ($banLeft > 0) {
    $min = (int) ceil($banLeft / 60);
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    header('Retry-After: ' . $banLeft);
    exit('<!DOCTYPE html><meta charset="utf-8"><title>Erişim engellendi</title><p style="font:16px sans-serif;margin:40px">Çok fazla hatalı giriş denemesi yapıldı. Bu IP adresinin panele erişimi ' . $min . ' dakika sonra açılacak.</p>');
}
start_session();

/** Bu IP için kalan ban süresi (saniye); ban yoksa 0 */
function ban_seconds_left(): int
{
    $until = (int) (Store::get('security')['banned'][client_ip()]['until'] ?? 0);
    return max(0, $until - time());
}

// ---------------------------------------------------------------- Oturum

function admin_user(): ?string
{
    if (empty($_SESSION['admin'])) {
        return null;
    }
    if (time() - ($_SESSION['admin_seen'] ?? 0) > SESSION_TTL) {
        logout();
        return null;
    }
    $_SESSION['admin_seen'] = time();
    return $_SESSION['admin'];
}

function require_admin(bool $json = false): string
{
    $u = admin_user();
    if ($u === null) {
        if ($json) {
            json_response(['ok' => false, 'message' => 'Oturum süreniz doldu. Sayfayı yenileyip tekrar giriş yapın.'], 401);
        }
        header('Location: login.php');
        exit;
    }
    return $u;
}

function logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

/** @return array{ok:bool, message?:string, banned?:bool} */
function attempt_login(string $username, string $password): array
{
    $ip = client_ip();
    $ok = false;
    foreach (Store::get('users')['users'] ?? [] as $u) {
        if (hash_equals(mb_strtolower($u['username']), mb_strtolower($username)) && password_verify($password, $u['hash'])) {
            $ok = true;
            $username = $u['username'];
            break;
        }
    }
    if (!$ok) {
        // Kullanıcı yoksa da hash süresi kadar bekle (zamanlama farkı olmasın)
        password_verify($password, '$2y$10$26FNKkHHCTPQEb6f2Axh4OlNjSF9GDY6obLfTg.48oBsJmo/V2wje');
    }

    $left = 0;
    $banned = false;
    Store::update('security', function (array $d) use ($ip, $ok, $username, &$left, &$banned) {
        $now = time();
        // Eski kayıt biçimini ve süresi geçmiş denemeleri temizle
        foreach ($d['fails'] ?? [] as $k => $times) {
            $d['fails'][$k] = array_values(array_filter((array) $times, fn($t) => is_int($t) && $t > $now - FAIL_WINDOW));
            if (!$d['fails'][$k]) {
                unset($d['fails'][$k]);
            }
        }
        unset($d['login']);
        // Süresi dolan banları sil (eski kalıcı kayıtlarda 'until' yoktur, onlar da süresi dolmuş sayılır)
        foreach ($d['banned'] ?? [] as $k => $b) {
            if ((int) ($b['until'] ?? 0) <= $now) {
                unset($d['banned'][$k]);
            }
        }
        if (empty($d['banned'])) {
            unset($d['banned']);
        }

        if ($ok) {
            unset($d['fails'][$ip]);
            return $d;
        }
        $d['fails'][$ip][] = $now;
        $count = count($d['fails'][$ip]);
        $left = MAX_LOGIN_FAILS - $count;
        if ($count >= MAX_LOGIN_FAILS) {
            $d['banned'][$ip] = ['date' => date('c'), 'until' => $now + BAN_SECONDS, 'fails' => $count, 'last_username' => mb_substr($username, 0, 60)];
            unset($d['fails'][$ip]);
            $banned = true;
        }
        return $d;
    });

    if ($banned) {
        return ['ok' => false, 'banned' => true, 'message' => 'Çok fazla hatalı giriş denemesi yapıldı. Bu IP adresinin panele erişimi ' . (BAN_SECONDS / 60) . ' dakika engellendi.'];
    }
    if (!$ok) {
        $msg = 'Kullanıcı adı ya da şifre hatalı.';
        if ($left <= 3) {
            $msg .= " {$left} deneme hakkınız kaldı; sonrasında bu IP adresi " . (BAN_SECONDS / 60) . ' dakika engellenecek.';
        }
        return ['ok' => false, 'message' => $msg];
    }
    session_regenerate_id(true);
    $_SESSION['admin'] = $username;
    $_SESSION['admin_seen'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return ['ok' => true];
}

// ---------------------------------------------------------------- Form üretici

function field_id(): string
{
    static $n = 0;
    return 'fld' . (++$n);
}

/**
 * Düzenleyici: üst seviye alanlar sekmelere ayrılır, her sekme ayrı bir bölmede gösterilir.
 * Sekmeler yalnızca görseldir; veri yapısı (.scope / .f) değişmez.
 */
function render_editor(array $fields, array $values): string
{
    $tabs = [];
    foreach ($fields as $f) {
        $tabs[$f['tab'] ?? $f['label']][] = $f;
    }
    $nav = '';
    $panes = '';
    $i = 0;
    foreach ($tabs as $title => $tabFields) {
        $id = 'pane-' . $i;
        $nav .= '<button type="button" class="ed-tab" role="tab" id="tab-' . $i . '" aria-controls="' . $id . '" aria-selected="' . ($i === 0 ? 'true' : 'false') . '">'
            . e($title) . '<span class="ed-tab-missing" hidden></span></button>';
        $body = '';
        foreach ($tabFields as $f) {
            // Sekmede tek bir grup varsa başlığı tekrar yazılmaz
            $body .= render_field($f, $values[$f['key']] ?? null, count($tabFields) === 1 ? 0 : 1);
        }
        $panes .= '<section class="ed-pane" id="' . $id . '" role="tabpanel" aria-labelledby="tab-' . $i . '"' . ($i === 0 ? '' : ' hidden') . '>'
            . '<h2 class="ed-pane-title">' . e($title) . '</h2>' . $body . '</section>';
        $i++;
    }
    return '<div class="ed-layout"><nav class="ed-tabs" role="tablist" aria-label="Bölümler">' . $nav . '</nav><div class="ed-panes">' . $panes . '</div></div>';
}

function render_fields(array $fields, array $values, int $depth = 1): string
{
    $html = '';
    foreach ($fields as $f) {
        $html .= render_field($f, $values[$f['key']] ?? null, $depth);
    }
    return $html;
}

function render_field(array $f, mixed $v, int $depth = 1): string
{
    $type = $f['type'];
    $help = !empty($f['help']) ? '<p class="help">' . e($f['help']) . '</p>' : '';
    $cls = 'f f-' . e($type) . (!empty($f['half']) ? ' f-half' : '') . (in_array($type, ['image', 'video', 'media'], true) ? ' f-mediafield' : '');
    $attrs = 'class="' . $cls . '" data-key="' . e($f['key']) . '" data-type="' . e($type) . '"' . (!empty($f['i18n']) ? ' data-i18n="1"' : '');

    if ($type === 'group') {
        $title = $depth > 0 ? '<h3 class="group-title">' . e($f['label']) . '</h3>' : '';
        return '<div ' . $attrs . ' data-depth="' . $depth . '">' . $title . $help
            . '<div class="scope group-body">' . render_fields($f['fields'], is_array($v) ? $v : [], $depth + 1) . '</div></div>';
    }

    if ($type === 'repeater') {
        $items = '';
        foreach (is_array($v) ? $v : [] as $item) {
            $items .= render_rep_item($f, is_array($item) ? $item : [], false, $depth);
        }
        $rcls = 'rep' . (!empty($f['compact']) ? ' rep-compact' : '') . (!empty($f['grid']) ? ' rep-grid' : '');
        return '<div ' . $attrs . ' data-depth="' . $depth . '"><div class="rep-header"><h3>' . e($f['label']) . ' <span class="count"></span></h3>'
            . '<button type="button" class="btn btn-sm rep-add">+ Yeni ekle</button></div>'
            . $help . (!empty($f['note']) ? '<p class="note">' . e($f['note']) . '</p>' : '')
            . '<div class="' . $rcls . '"><div class="rep-items">' . $items . '</div>'
            . '<template>' . render_rep_item($f, [], true, $depth) . '</template></div></div>';
    }

    $id = field_id();

    if (!empty($f['i18n'])) {
        // Her iki dilin kutusu da DOM'da durur; dil seçiciye göre yalnızca biri görünür
        $v = is_array($v) ? $v : ['tr' => (string) $v, 'en' => ''];
        $ctl = '<div class="ctl i18n">';
        foreach (LANGS as $l) {
            $ctl .= '<div class="lang-row" data-lang-row="' . $l . '">' . input_html($f, (string) ($v[$l] ?? ''), $id . '-' . $l, $l) . '</div>';
        }
        $label = '<label class="lbl" for="' . $id . '-tr" data-for="' . $id . '">' . e($f['label']) . ' <span class="lang-chip" aria-hidden="true"></span></label>';
        return '<div ' . $attrs . '>' . $label . $ctl . '</div>' . $help . '</div>';
    }

    if ($type === 'checkbox') {
        $checked = ($v === null ? !empty($f['default']) : (bool) $v) ? ' checked' : '';
        return '<div ' . $attrs . '><label class="switch"><input type="checkbox" class="v" id="' . $id . '"' . $checked . '><span>' . e($f['label']) . '</span></label>' . $help . '</div>';
    }

    if (in_array($type, ['image', 'video', 'media'], true)) {
        $v = (string) $v;
        $accept = ['image' => 'image/*', 'video' => 'video/mp4,video/webm', 'media' => 'image/*,video/mp4,video/webm'][$type];
        $kind = ['image' => 'Görsel', 'video' => 'Video', 'media' => 'Görsel ya da video'][$type];
        return '<div ' . $attrs . '><div class="ctl media-card">'
            . '<div class="preview">' . preview_html($v) . '</div>'
            . '<div class="media-info">'
            . '<p class="lbl media-label">' . e($f['label']) . ($type === 'media' ? ' <span class="kind">' . e($kind) . '</span>' : '') . '</p>'
            . '<p class="file-name">' . ($v !== '' ? e(basename($v)) : '<span class="muted">Dosya seçilmedi</span>') . '</p>'
            . '<div class="media-btns"><label class="btn btn-sm">Bilgisayardan yükle<input type="file" class="upload" accept="' . $accept . '" hidden></label>'
            . '<button type="button" class="btn btn-sm btn-ghost pick" data-kind="' . e($type) . '">Kütüphaneden seç</button>'
            . '<button type="button" class="btn btn-sm btn-ghost clear">Kaldır</button></div>'
            . $help
            . '<details class="path-edit"><summary>Dosya yolunu elle yaz</summary><input type="text" class="v" id="' . $id . '" value="' . e($v) . '" placeholder="uploads/dosya.jpg" spellcheck="false"></details>'
            . '</div></div></div>';
    }

    $label = '<label class="lbl" for="' . $id . '">' . e($f['label']) . '</label>';
    return '<div ' . $attrs . '>' . $label . '<div class="ctl">' . input_html($f, is_scalar($v) ? (string) $v : '', $id) . '</div>' . $help . '</div>';
}

function render_rep_item(array $f, array $item, bool $blank = false, int $depth = 1): string
{
    $collapsed = !empty($f['collapsed']) && !$blank ? ' collapsed' : '';
    return '<div class="rep-item' . $collapsed . '" data-label-key="' . e($f['item_label'] ?? '') . '">'
        . '<div class="rep-head"><button type="button" class="rep-toggle" aria-label="Aç/kapat"></button><span class="rep-title"></span>'
        . '<span class="rep-tools"><button type="button" class="up" title="Yukarı taşı">↑</button><button type="button" class="down" title="Aşağı taşı">↓</button>'
        . '<button type="button" class="del" title="Sil">Sil</button></span></div>'
        . '<div class="scope rep-body">' . render_fields($f['fields'], $item, $depth + 1) . '</div></div>';
}

function input_html(array $f, string $v, string $id, string $lang = ''): string
{
    $l = $lang !== '' ? ' data-lang="' . $lang . '" lang="' . $lang . '"' : '';
    switch ($f['type']) {
        case 'textarea':
            return '<textarea class="v" id="' . $id . '" rows="' . (int) ($f['rows'] ?? 4) . '"' . $l . '>' . e($v) . '</textarea>';
        case 'select':
            $o = '';
            foreach ($f['options'] ?? [] as $opt) {
                $o .= '<option value="' . e($opt['value']) . '"' . ($opt['value'] === $v ? ' selected' : '') . '>' . e($opt['label']) . '</option>';
            }
            return '<select class="v" id="' . $id . '">' . $o . '</select>';
        case 'password':
            return '<input type="password" class="v" id="' . $id . '" value="" autocomplete="new-password" placeholder="' . ($v !== '' ? '•••••••• (kayıtlı)' : '') . '">';
        case 'number':
            $v = $v === '' && isset($f['default']) ? (string) $f['default'] : $v;
            return '<input type="number" class="v" id="' . $id . '" value="' . e($v) . '"'
                . (isset($f['min']) ? ' min="' . (int) $f['min'] . '"' : '') . (isset($f['max']) ? ' max="' . (int) $f['max'] . '"' : '') . '>';
        case 'slug':
            return '<input type="text" class="v" id="' . $id . '" value="' . e($v) . '" data-from="' . e($f['from'] ?? '') . '" spellcheck="false">';
        default:
            $t = in_array($f['type'], ['url', 'email'], true) ? $f['type'] : 'text';
            return '<input type="' . $t . '" class="v" id="' . $id . '" value="' . e($v) . '"' . $l . '>';
    }
}

function preview_html(string $path): string
{
    if ($path === '') {
        return '<span class="empty-prev">Dosya yok</span>';
    }
    return is_video($path)
        ? '<video src="' . e(media($path)) . '" muted preload="metadata"></video><span class="badge-kind">Video</span>'
        : '<img src="' . e(media($path)) . '" alt=""><span class="badge-kind">Görsel</span>';
}

// ---------------------------------------------------------------- Doğrulama

final class ValidationError extends RuntimeException
{
}

function clean_text(mixed $v, bool $multiline): string
{
    $s = is_scalar($v) ? (string) $v : '';
    $s = str_replace("\r\n", "\n", $s);
    $s = preg_replace($multiline ? '/[\x00-\x09\x0B-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u', '', $s) ?? '';
    return mb_substr(trim($s), 0, 20000);
}

function clean_path(string $v, string $type, string $label): string
{
    $v = trim($v);
    if ($v === '') {
        return '';
    }
    $ext = strtolower(pathinfo($v, PATHINFO_EXTENSION));
    $allowed = ['image' => IMAGE_EXT, 'video' => VIDEO_EXT, 'media' => array_merge(IMAGE_EXT, VIDEO_EXT)][$type];
    if (!preg_match('#^uploads/[A-Za-z0-9._-]+$#', $v) || !in_array($ext, $allowed, true)) {
        throw new ValidationError("“{$label}” alanındaki dosya geçersiz. Dosyayı yükle butonuyla ya da kütüphaneden seçin.");
    }
    if (!is_file(ROOT . '/' . $v)) {
        throw new ValidationError("“{$label}” alanındaki dosya bulunamadı: {$v}");
    }
    return $v;
}

function sanitize(array $fields, mixed $input, array $old, string $path = ''): array
{
    $input = is_array($input) ? $input : [];
    $out = [];
    foreach ($fields as $f) {
        $k = $f['key'];
        $v = $input[$k] ?? null;
        $label = trim($path . ' › ' . $f['label'], ' ›');
        $multi = $f['type'] === 'textarea';

        if (!empty($f['i18n'])) {
            $v = is_array($v) ? $v : [];
            $out[$k] = [];
            foreach (LANGS as $l) {
                $out[$k][$l] = clean_text($v[$l] ?? '', $multi);
            }
            continue;
        }

        switch ($f['type']) {
            case 'group':
                $out[$k] = sanitize($f['fields'], $v, is_array($old[$k] ?? null) ? $old[$k] : [], $label);
                break;
            case 'repeater':
                $items = [];
                foreach (array_values(is_array($v) ? $v : []) as $i => $item) {
                    $items[] = sanitize($f['fields'], $item, [], $label . ' #' . ($i + 1));
                }
                $out[$k] = unique_slugs($f, $items);
                break;
            case 'checkbox':
                $out[$k] = (bool) $v;
                break;
            case 'number':
                // Alan formda yoksa ya da boş bırakıldıysa kayıtlı değeri, o da yoksa varsayılanı koru
                // (aksi halde boş değer 0 olup min sınırına yuvarlanıyordu)
                if ($v === null || trim((string) $v) === '') {
                    $v = $old[$k] ?? ($f['default'] ?? 0);
                }
                $n = (int) $v;
                if (isset($f['min'])) {
                    $n = max($f['min'], $n);
                }
                if (isset($f['max'])) {
                    $n = min($f['max'], $n);
                }
                $out[$k] = $n;
                break;
            case 'select':
                $vals = array_column($f['options'] ?? [], 'value');
                $v = (string) $v;
                $out[$k] = in_array($v, $vals, true) ? $v : (string) ($vals[0] ?? '');
                break;
            case 'url':
                $v = clean_text($v, false);
                if ($v !== '' && (!filter_var($v, FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $v))) {
                    throw new ValidationError("“{$label}” geçerli bir adres değil. https:// ile başlayan tam adresi girin.");
                }
                if ($k === 'map_embed' && $v !== '' && !preg_match('#^https://(www\.)?google\.[a-z.]+/maps/embed#i', $v)) {
                    throw new ValidationError('Harita adresi https://www.google.com/maps/embed ile başlamalı.');
                }
                $out[$k] = $v;
                break;
            case 'email':
                $v = clean_text($v, false);
                if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidationError("“{$label}” geçerli bir e-posta adresi değil.");
                }
                $out[$k] = $v;
                break;
            case 'password':
                $v = is_string($v) ? $v : '';
                $out[$k] = $v === '' ? (string) ($old[$k] ?? '') : $v;
                break;
            case 'image':
            case 'video':
            case 'media':
                $out[$k] = clean_path(clean_text($v, false), $f['type'], $label);
                break;
            case 'slug':
                $out[$k] = slugify(clean_text($v, false));
                break;
            default:
                $out[$k] = clean_text($v, $multi);
        }
    }

    // Boş slug'ları kaynak alandan üret
    foreach ($fields as $f) {
        if ($f['type'] === 'slug' && ($out[$f['key']] ?? '') === '' && !empty($f['from'])) {
            $src = $out[$f['from']] ?? '';
            $out[$f['key']] = slugify(is_array($src) ? ($src['tr'] ?: $src['en']) : (string) $src);
        }
    }
    return $out;
}

/** Aynı listede tekrar eden slug'lara -2, -3 ekler */
function unique_slugs(array $repeater, array $items): array
{
    foreach ($repeater['fields'] as $f) {
        if ($f['type'] !== 'slug') {
            continue;
        }
        $seen = [];
        foreach ($items as &$it) {
            $base = $it[$f['key']] ?: 'kayit';
            $s = $base;
            $n = 2;
            while (isset($seen[$s])) {
                $s = $base . '-' . $n++;
            }
            $seen[$s] = true;
            $it[$f['key']] = $s;
        }
        unset($it);
    }
    return $items;
}

// ---------------------------------------------------------------- Yardımcılar

function upload_files(): array
{
    $files = [];
    foreach (glob(UPLOADS . '/*') ?: [] as $p) {
        $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
        if (is_file($p) && in_array($ext, array_merge(IMAGE_EXT, VIDEO_EXT), true)) {
            $files[] = ['path' => 'uploads/' . basename($p), 'size' => filesize($p), 'time' => filemtime($p), 'video' => in_array($ext, VIDEO_EXT, true)];
        }
    }
    usort($files, fn($a, $b) => $b['time'] <=> $a['time']);
    return $files;
}

/** Bir dosyanın JSON içeriklerinde kullanılıp kullanılmadığını bulur */
function file_usage(string $path): array
{
    $used = [];
    foreach (['home', 'pages', 'applications', 'products', 'gallery', 'settings'] as $name) {
        $raw = is_file(Store::path($name)) ? (string) file_get_contents(Store::path($name)) : '';
        if (str_contains($raw, '"' . $path . '"')) {
            $used[] = panel_sections()[$name]['title'] ?? $name;
        }
    }
    return $used;
}

function human_size(int $b): string
{
    return $b >= 1048576 ? round($b / 1048576, 1) . ' MB' : max(1, round($b / 1024)) . ' KB';
}

function unread_count(): int
{
    return count(array_filter(Store::get('messages'), fn($m) => empty($m['read'])));
}
