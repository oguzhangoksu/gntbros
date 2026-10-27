<?php
declare(strict_types=1);

require __DIR__ . '/lib.php';
require_once APP . '/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Yalnızca POST'], 405);
}
$user = require_admin(true);
if (!csrf_check($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_token'] ?? null))) {
    json_response(['ok' => false, 'message' => 'Güvenlik anahtarı geçersiz. Sayfayı yenileyip tekrar deneyin.'], 419);
}

$action = (string) ($_GET['action'] ?? '');

try {
    switch ($action) {
        case 'save':
            $sections = panel_sections();
            $key = (string) ($_GET['section'] ?? '');
            if (!isset($sections[$key])) {
                json_response(['ok' => false, 'message' => 'Bilinmeyen bölüm'], 404);
            }
            $sec = $sections[$key];
            $input = json_decode((string) ($_POST['data'] ?? ''), true);
            if (!is_array($input)) {
                json_response(['ok' => false, 'message' => 'Gönderilen veri okunamadı.'], 400);
            }
            $old = Store::get($sec['file']);
            $clean = sanitize($sec['fields'], $input, $old);

            $warn = [];
            if ($key === 'products') {
                $cats = array_column($clean['categories'], 'slug');
                foreach ($clean['items'] as $i => $it) {
                    if (!in_array($it['category'], $cats, true)) {
                        $warn[] = '“' . ($it['name']['tr'] ?: 'Ürün #' . ($i + 1)) . '” bir kategoriye bağlı değil, sitede görünmez.';
                    }
                }
            }
            Store::put($sec['file'], $clean);
            json_response(['ok' => true, 'message' => 'Değişiklikler kaydedildi.', 'warnings' => $warn, 'reload' => $key === 'products']);

        case 'upload':
            $f = $_FILES['file'] ?? null;
            if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                $code = $f['error'] ?? UPLOAD_ERR_NO_FILE;
                $msg = in_array($code, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                    ? 'Dosya çok büyük. Sunucunun izin verdiği en büyük boyut: ' . ini_get('upload_max_filesize')
                    : 'Dosya yüklenemedi. Tekrar deneyin.';
                json_response(['ok' => false, 'message' => $msg], 400);
            }
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']) ?: '';
            if (!isset(UPLOAD_TYPES[$mime])) {
                json_response(['ok' => false, 'message' => 'Bu dosya türü desteklenmiyor. JPG, PNG, WEBP, AVIF, GIF, MP4 ya da WEBM yükleyin.'], 415);
            }
            $isVideo = str_starts_with($mime, 'video/');
            $limit = $isVideo ? 60 * 1048576 : 8 * 1048576;
            if ($f['size'] > $limit) {
                json_response(['ok' => false, 'message' => 'Dosya çok büyük. En fazla ' . ($isVideo ? '60' : '8') . ' MB yükleyebilirsiniz.'], 413);
            }
            if (!$isVideo && $mime !== 'image/x-icon' && $mime !== 'image/vnd.microsoft.icon' && @getimagesize($f['tmp_name']) === false && $mime !== 'image/avif') {
                json_response(['ok' => false, 'message' => 'Görsel dosyası okunamadı.'], 415);
            }
            $base = slugify(pathinfo((string) $f['name'], PATHINFO_FILENAME)) ?: 'dosya';
            $base = substr($base, 0, 60);
            $ext = UPLOAD_TYPES[$mime];
            $name = $base . '.' . $ext;
            for ($n = 2; is_file(UPLOADS . '/' . $name); $n++) {
                $name = $base . '-' . $n . '.' . $ext;
            }
            if (!move_uploaded_file($f['tmp_name'], UPLOADS . '/' . $name)) {
                json_response(['ok' => false, 'message' => 'Dosya kaydedilemedi. uploads klasörünün yazma iznini kontrol edin.'], 500);
            }
            json_response(['ok' => true, 'path' => 'uploads/' . $name, 'url' => media('uploads/' . $name), 'video' => $isVideo]);

        case 'library':
            json_response(['ok' => true, 'files' => array_map(fn($x) => $x + ['url' => media($x['path'])], upload_files())]);

        case 'delete_file':
            $path = (string) ($_POST['path'] ?? '');
            if (!preg_match('#^uploads/[A-Za-z0-9._-]+$#', $path) || !is_file(ROOT . '/' . $path)) {
                json_response(['ok' => false, 'message' => 'Dosya bulunamadı.'], 404);
            }
            $used = file_usage($path);
            if ($used && empty($_POST['force'])) {
                json_response(['ok' => false, 'used' => $used, 'message' => 'Bu dosya şu bölümlerde kullanılıyor: ' . implode(', ', $used) . '. Önce oradan kaldırın.'], 409);
            }
            unlink(ROOT . '/' . $path);
            json_response(['ok' => true, 'message' => 'Dosya silindi.']);

        case 'message':
            $id = (string) ($_POST['id'] ?? '');
            $op = (string) ($_POST['op'] ?? '');
            Store::update('messages', function (array $list) use ($id, $op) {
                foreach ($list as $i => $m) {
                    if (($m['id'] ?? '') !== $id) {
                        continue;
                    }
                    if ($op === 'delete') {
                        unset($list[$i]);
                    } else {
                        $list[$i]['read'] = $op === 'read';
                    }
                }
                return array_values($list);
            });
            json_response(['ok' => true, 'unread' => unread_count()]);

        case 'test_mail':
            $smtp = settings()['smtp'] ?? [];
            if (empty($smtp['host']) || empty($smtp['to'])) {
                json_response(['ok' => false, 'message' => 'Önce SMTP sunucusu ve alıcı adresini girip kaydedin.'], 422);
            }
            $m = new Mailer($smtp);
            $sent = $m->send('GNT Broş panel – deneme e-postası', '<p>Bu bir deneme e-postasıdır. SMTP ayarlarınız çalışıyor.</p>');
            json_response(['ok' => $sent, 'message' => $sent ? 'Deneme e-postası gönderildi: ' . $smtp['to'] : 'Gönderilemedi.', 'log' => $sent ? '' : $m->lastLog()], $sent ? 200 : 502);

        default:
            json_response(['ok' => false, 'message' => 'Bilinmeyen işlem'], 404);
    }
} catch (ValidationError $ex) {
    json_response(['ok' => false, 'message' => $ex->getMessage()], 422);
} catch (Throwable $ex) {
    error_log('[gntbros panel] ' . $ex);
    json_response(['ok' => false, 'message' => 'Beklenmeyen bir hata oluştu: ' . $ex->getMessage()], 500);
}
