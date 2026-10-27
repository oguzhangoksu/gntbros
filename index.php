<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

// --- İstek yolunu çöz ---
$uri = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$base = base_path();
if ($base !== '' && stripos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}
$path = trim($uri, '/');
if ($path === 'index.php') {
    $path = '';
}

// Eski sitenin .php adreslerini yeni adreslere kalıcı yönlendir
$legacy = ['hakkimizda' => 'about', 'vizyon-misyon' => 'vision', 'kurulus-politikamiz' => 'policy',
    'uygulamalarimiz' => 'applications', 'urunlerimiz' => 'products', 'iletisim' => 'contact'];
if (preg_match('/^([a-z-]+)\.php$/', $path, $m) && isset($legacy[$m[1]])) {
    header('Location: ' . url($legacy[$m[1]], '', 'tr'), true, 301);
    exit;
}

set_lang(($path === 'en' || str_starts_with($path, 'en/')) ? 'en' : 'tr');

$pageKey = null;
$param = '';
$pageNo = 1;
foreach (ROUTES as $key => $paths) {
    $p = $paths[lang()];
    if ($path === $p) {
        $pageKey = $key;
        break;
    }
    if (in_array($key, ['products', 'product'], true) && $p !== '' && str_starts_with($path, $p . '/')) {
        $rest = substr($path, strlen($p) + 1);
        // Ürün listesi sayfalama: [kategori/]sayfa/N
        if ($key === 'products' && preg_match('#^(?:([a-z0-9-]+)/)?' . PAGE_WORD[lang()] . '/([0-9]{1,4})$#', $rest, $m)) {
            $pageKey = $key;
            $param = $m[1];
            $pageNo = (int) $m[2];
            if ($pageNo <= 1) {
                header('Location: ' . products_url($param), true, 301);
                exit;
            }
            break;
        }
        if (preg_match('/^[a-z0-9-]+$/', $rest)) {
            $pageKey = $key;
            $param = $rest;
            break;
        }
    }
}
if ($pageKey === 'product' && $param === '') {
    $pageKey = null;
}

// --- Sayfa verisi ---
$settings = settings();
$seo = $settings['seo'][$pageKey] ?? [];
$alt = []; // diğer dildeki karşılık
$view = $pageKey;
$data = [];

switch ($pageKey) {
    case 'home':
        $data = Store::get('home');
        break;
    case 'about':
    case 'vision':
    case 'policy':
        $data = Store::get('pages')[$pageKey] ?? [];
        break;
    case 'applications':
        $data = Store::get('applications');
        break;
    case 'gallery':
        $data = Store::get('gallery');
        break;
    case 'products':
        $data = Store::get('products');
        $data['categories'] = active_items($data['categories'] ?? []);
        $activeCats = array_column($data['categories'], 'slug');
        $data['items'] = array_values(array_filter(
            active_items($data['items'] ?? []),
            fn($i) => in_array($i['category'] ?? '', $activeCats, true)
        ));
        $data['current'] = null;
        if ($param !== '') {
            foreach ($data['categories'] as $c) {
                if ($c['slug'] === $param) {
                    $data['current'] = $c;
                }
            }
            if (!$data['current']) {
                $view = null;
                break;
            }
            $data['items'] = array_values(array_filter($data['items'], fn($i) => $i['category'] === $param));
            $seo = ['title' => t($data['current']['name']) . ' | ' . t($seo['title'] ?? ''), 'description' => $seo['description'] ?? ''];
        }

        // Sayfalama: ürün sayısı sayfa başına adedi aşarsa listeyi böl
        $perPage = max(1, (int) ($data['per_page'] ?? 10));
        $total = count($data['items']);
        $pages = max(1, (int) ceil($total / $perPage));
        if ($pageNo > $pages) {
            $view = null;
            break;
        }
        $data['items'] = array_slice($data['items'], ($pageNo - 1) * $perPage, $perPage);
        $data['pager'] = ['page' => $pageNo, 'pages' => $pages, 'total' => $total, 'per_page' => $perPage, 'cat' => $param];
        if ($pageNo > 1) {
            // "Ürünlerimiz | GNT Broş" → "Ürünlerimiz – Sayfa 2 | GNT Broş"
            $parts = explode(' | ', t($seo['title'] ?? ''), 2);
            $parts[0] .= ' – ' . __('page') . ' ' . $pageNo;
            $seo['title'] = implode(' | ', $parts);
        }
        break;
    case 'product':
        $all = Store::get('products');
        $cats = array_column(active_items($all['categories'] ?? []), null, 'slug');
        foreach (active_items($all['items'] ?? []) as $i) {
            if ($i['slug'] === $param && isset($cats[$i['category']])) {
                $data = ['item' => $i, 'category' => $cats[$i['category']]];
            }
        }
        if (!$data) {
            $view = null;
            break;
        }
        $data['related'] = array_slice(array_values(array_filter(
            active_items($all['items']),
            fn($i) => $i['category'] === $data['item']['category'] && $i['slug'] !== $param
        )), 0, 3);
        $seo = [
            'title'       => t($data['item']['name']) . ' | ' . $settings['site']['name'],
            'description' => t($data['item']['summary'] ?? ''),
            'image'       => $data['item']['images'][0]['image'] ?? '',
        ];
        break;
    case 'contact':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require APP . '/contact.php';
            handle_contact($settings);
        }
        start_session();
        $data['flash'] = $_SESSION['contact_flash'] ?? null;
        unset($_SESSION['contact_flash']);
        break;
}

if ($view === null) {
    http_response_code(404);
    $view = '404';
    $pageKey = null;
    $seo = ['title' => __('not_found') . ' | ' . $settings['site']['name']];
}

$relLinks = []; // <link rel="prev/next"> sayfalama ipuçları
if ($pageKey !== null) {
    foreach (LANGS as $l) {
        $alt[$l] = $pageKey === 'products' ? products_url($param, $pageNo, $l) : url($pageKey, $param, $l);
    }
    if ($pageKey === 'products') {
        $pg = $data['pager'];
        if ($pg['page'] > 1) {
            $relLinks['prev'] = products_url($param, $pg['page'] - 1);
        }
        if ($pg['page'] < $pg['pages']) {
            $relLinks['next'] = products_url($param, $pg['page'] + 1);
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');

require APP . '/views/layout/header.php';
require APP . '/views/pages/' . $view . '.php';
require APP . '/views/layout/footer.php';
