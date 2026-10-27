<?php
declare(strict_types=1);

require_once APP . '/mailer.php';

/** İletişim formunu işler: doğrular, data/messages.json'a kaydeder, SMTP açıksa mail atar. */
function handle_contact(array $settings): never
{
    $ajax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    $in = fn(string $k, int $max) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);

    $fail = function (string $key, int $status = 422) use ($ajax): never {
        if ($ajax) {
            json_response(['ok' => false, 'message' => __($key)], $status);
        }
        $_SESSION['contact_flash'] = ['ok' => false, 'message' => __($key), 'old' => $_POST];
        header('Location: ' . url('contact'), true, 303);
        exit;
    };

    start_session();
    if (!csrf_check($_POST['_token'] ?? null)) {
        $fail('err_token', 419);
    }

    // Bot tuzağı: dolu gelirse sessizce başarılı say
    $honeypot = trim((string) ($_POST['website'] ?? ''));
    $tooFast = (time() - (int) ($_POST['_ts'] ?? 0)) < 3;

    $msg = [
        'name'    => $in('name', 120),
        'company' => $in('company', 160),
        'email'   => $in('email', 160),
        'phone'   => $in('phone', 40),
        'subject' => $in('subject', 200),
        'message' => $in('message', 5000),
    ];

    if ($msg['name'] === '' || $msg['email'] === '' || $msg['subject'] === '' || $msg['message'] === '') {
        $fail('err_required');
    }
    if (!filter_var($msg['email'], FILTER_VALIDATE_EMAIL)) {
        $fail('err_email');
    }
    if (empty($_POST['consent'])) {
        $fail('err_kvkk');
    }

    if ($honeypot === '' && !$tooFast) {
        // IP başına 10 dakikada en fazla 5 mesaj
        $ipHash = hash('sha256', client_ip() . '|gnt');
        $limited = false;
        Store::update('ratelimit', function (array $d) use ($ipHash, &$limited) {
            $now = time();
            foreach ($d as $k => $times) {
                $d[$k] = array_values(array_filter((array) $times, fn($t) => $t > $now - 600));
                if (!$d[$k]) {
                    unset($d[$k]);
                }
            }
            if (count($d[$ipHash] ?? []) >= 5) {
                $limited = true;
            } else {
                $d[$ipHash][] = $now;
            }
            return $d;
        });
        if ($limited) {
            $fail('err_rate', 429);
        }

        $record = $msg + [
            'id'   => bin2hex(random_bytes(8)),
            'date' => date('c'),
            'lang' => lang(),
            'ip'   => client_ip(),
            'read' => false,
            'mail' => null,
        ];

        $smtp = $settings['smtp'] ?? [];
        if (Mailer::configured($smtp)) {
            $mailer = new Mailer($smtp);
            $record['mail'] = $mailer->send(
                'Web sitesi mesajı: ' . $msg['subject'],
                contact_mail_html($msg, $settings),
                $msg['email'],
                $msg['name']
            ) ? 'sent' : 'failed';
            if ($record['mail'] === 'failed') {
                error_log('[gntbros] SMTP: ' . $mailer->lastLog());
            }
        }

        Store::update('messages', function (array $d) use ($record) {
            array_unshift($d, $record);
            return $d;
        });
    }

    if ($ajax) {
        json_response(['ok' => true, 'message' => __('form_ok')]);
    }
    $_SESSION['contact_flash'] = ['ok' => true, 'message' => __('form_ok')];
    header('Location: ' . url('contact'), true, 303);
    exit;
}

function contact_mail_html(array $m, array $settings): string
{
    $rows = [
        'Ad soyad' => $m['name'],
        'Firma'    => $m['company'],
        'E-posta'  => $m['email'],
        'Telefon'  => $m['phone'],
        'Konu'     => $m['subject'],
    ];
    $html = '<div style="font-family:Arial,sans-serif;font-size:15px;color:#23303b;max-width:640px">';
    $html .= '<h2 style="border-bottom:3px solid #c8161d;padding-bottom:8px">' . e($settings['site']['name'] ?? '') . ' – yeni mesaj</h2>';
    $html .= '<table cellpadding="6" style="border-collapse:collapse">';
    foreach ($rows as $k => $v) {
        if ($v !== '') {
            $html .= '<tr><td style="color:#5a6a78"><strong>' . e($k) . '</strong></td><td>' . e($v) . '</td></tr>';
        }
    }
    $html .= '</table><p style="white-space:pre-wrap;background:#f1f3f5;padding:14px">' . e($m['message']) . '</p>';
    $html .= '<p style="color:#5a6a78;font-size:12px">' . date('d.m.Y H:i') . ' – ' . e(client_ip()) . '</p></div>';
    return $html;
}
