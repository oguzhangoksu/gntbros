<?php
declare(strict_types=1);

/**
 * JSON dosya deposu. Tüm içerik data/ altında tutulur.
 * Yazma işlemleri kilitli yapılır ve her kayıttan önce yedek alınır.
 */
final class Store
{
    private static array $cache = [];

    public static function path(string $name): string
    {
        if (!preg_match('/^[a-z0-9_-]+$/', $name)) {
            throw new InvalidArgumentException('Geçersiz veri dosyası adı');
        }
        return DATA . '/' . $name . '.json';
    }

    public static function get(string $name, array $default = []): array
    {
        if (array_key_exists($name, self::$cache)) {
            return self::$cache[$name];
        }
        $file = self::path($name);
        if (!is_file($file)) {
            return self::$cache[$name] = $default;
        }
        $fp = fopen($file, 'rb');
        flock($fp, LOCK_SH);
        $raw = stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        $data = json_decode((string) $raw, true);
        return self::$cache[$name] = is_array($data) ? $data : $default;
    }

    public static function put(string $name, array $data, bool $backup = true): void
    {
        $file = self::path($name);
        if ($backup && is_file($file)) {
            self::backup($name, $file);
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('JSON kodlanamadı');
        }
        $fp = fopen($file, 'cb');
        if (!$fp || !flock($fp, LOCK_EX)) {
            throw new RuntimeException('Veri dosyası kilitlenemedi: ' . $name);
        }
        ftruncate($fp, 0);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        self::$cache[$name] = $data;
    }

    /** Okuma-değiştirme-yazma işlemini tek kilit altında yapar (mesajlar, sayaçlar için). */
    public static function update(string $name, callable $fn): array
    {
        $file = self::path($name);
        $fp = fopen($file, 'c+b');
        if (!$fp || !flock($fp, LOCK_EX)) {
            throw new RuntimeException('Veri dosyası kilitlenemedi: ' . $name);
        }
        $data = json_decode((string) stream_get_contents($fp), true);
        $data = $fn(is_array($data) ? $data : []);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return self::$cache[$name] = $data;
    }

    private static function backup(string $name, string $file): void
    {
        $dir = DATA . '/backups';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        copy($file, $dir . '/' . $name . '-' . date('Ymd-His') . '.json');

        // Her dosya için son 20 yedeği tut
        $old = glob($dir . '/' . $name . '-*.json') ?: [];
        sort($old);
        foreach (array_slice($old, 0, max(0, count($old) - 20)) as $f) {
            @unlink($f);
        }
    }
}
