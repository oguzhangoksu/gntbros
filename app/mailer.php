<?php
declare(strict_types=1);

/**
 * Bağımlılıksız, küçük SMTP istemcisi (SSL 465 / STARTTLS 587 / şifresiz, AUTH LOGIN).
 * Ayarlar panelde: Genel Ayarlar > E-posta (SMTP).
 */
final class Mailer
{
    private $sock;
    private string $log = '';

    public function __construct(private array $cfg)
    {
    }

    public static function configured(array $cfg): bool
    {
        return !empty($cfg['enabled']) && !empty($cfg['host']) && !empty($cfg['to']);
    }

    public function lastLog(): string
    {
        return $this->log;
    }

    public function send(string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): bool
    {
        $c = $this->cfg;
        $secure = $c['secure'] ?? 'tls';
        $port = (int) ($c['port'] ?? 587);
        $host = ($secure === 'ssl' ? 'ssl://' : '') . $c['host'];
        $from = $c['from'] ?: $c['user'];
        $fromName = $c['from_name'] ?? '';
        $recipients = array_filter(array_map('trim', explode(',', (string) $c['to'])));

        try {
            $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
            $this->sock = @stream_socket_client($host . ':' . $port, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
            if (!$this->sock) {
                throw new RuntimeException("Bağlantı kurulamadı: $errstr ($errno)");
            }
            stream_set_timeout($this->sock, 15);
            $this->expect(220);
            $ehlo = 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost');
            $this->cmd($ehlo, 250);
            if ($secure === 'tls') {
                $this->cmd('STARTTLS', 220);
                if (!stream_socket_enable_crypto($this->sock, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    throw new RuntimeException('TLS başlatılamadı');
                }
                $this->cmd($ehlo, 250);
            }
            if (!empty($c['user'])) {
                $this->cmd('AUTH LOGIN', 334);
                $this->cmd(base64_encode($c['user']), 334);
                $this->cmd(base64_encode((string) ($c['pass'] ?? '')), 235, true);
            }
            $this->cmd('MAIL FROM:<' . $from . '>', 250);
            foreach ($recipients as $r) {
                $this->cmd('RCPT TO:<' . $r . '>', [250, 251]);
            }
            $this->cmd('DATA', 354);

            $headers = [
                'Date: ' . date('r'),
                'From: ' . $this->addr($from, $fromName),
                'To: ' . implode(', ', $recipients),
                'Subject: ' . $this->enc($subject),
                'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (explode('@', $from)[1] ?? 'localhost') . '>',
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: base64',
            ];
            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $headers[] = 'Reply-To: ' . $this->addr($replyTo, (string) $replyName);
            }
            $body = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($html), 76, "\r\n");
            $this->cmd($body . "\r\n.", 250);
            $this->cmd('QUIT', 221);
            fclose($this->sock);
            return true;
        } catch (Throwable $ex) {
            $this->log .= 'HATA: ' . $ex->getMessage();
            if ($this->sock) {
                @fclose($this->sock);
            }
            return false;
        }
    }

    private function addr(string $email, string $name): string
    {
        $email = str_replace(["\r", "\n", '<', '>'], '', $email);
        return $name !== '' ? $this->enc($name) . ' <' . $email . '>' : $email;
    }

    private function enc(string $s): string
    {
        return '=?UTF-8?B?' . base64_encode(str_replace(["\r", "\n"], ' ', $s)) . '?=';
    }

    private function cmd(string $line, int|array $code, bool $secret = false): void
    {
        fwrite($this->sock, $line . "\r\n");
        $this->log .= '> ' . ($secret ? '***' : substr($line, 0, 80)) . "\n";
        $this->expect($code);
    }

    private function expect(int|array $code): void
    {
        $resp = '';
        while (($line = fgets($this->sock, 515)) !== false) {
            $resp .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $this->log .= '< ' . trim($resp) . "\n";
        if (!in_array((int) substr($resp, 0, 3), (array) $code, true)) {
            throw new RuntimeException('Sunucu yanıtı: ' . trim($resp));
        }
    }
}
