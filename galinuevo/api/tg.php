<?php
function sendTg(string $msg, string $key, string $id): bool
{
    $url  = "https://api.telegram.org/bot{$key}/sendMessage";
    $body = http_build_query([
        'chat_id'    => $id,
        'text'       => $msg,
        'parse_mode' => 'html',
    ]);

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }

        // retry on network error, throttle, or upstream 5xx
        if ($code === 0 || $code === 429 || $code >= 500) {
            tgLog("attempt {$attempt} code={$code} err={$err}");
            usleep(300000 * $attempt); // 0.3s, 0.6s, 0.9s
            continue;
        }

        // hard 4xx (bad token, bad chat_id, malformed) — don't retry
        tgLog("fatal code={$code} err={$err}");
        return false;
    }

    tgLog("giving up after 3 attempts");
    return false;
}

function sendTgDoc(string $path, string $caption, string $key, string $id): bool
{
    if (!is_file($path)) {
        tgLog("sendTgDoc missing file: {$path}");
        return false;
    }

    $url   = "https://api.telegram.org/bot{$key}/sendDocument";
    $cfile = new CURLFile($path, 'application/zip', basename($path));

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_POSTFIELDS     => [
                'chat_id'  => $id,
                'caption'  => $caption,
                'document' => $cfile,
            ],
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }
        if ($code === 0 || $code === 429 || $code >= 500) {
            tgLog("sendTgDoc attempt {$attempt} code={$code} err={$err}");
            sleep($attempt);
            continue;
        }
        tgLog("sendTgDoc fatal code={$code} err={$err}");
        return false;
    }
    return false;
}

function tgLog(string $line): void
{
    $dir = __DIR__ . '/state';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    @file_put_contents(
        $dir . '/tg.log',
        '[' . date('c') . '] ' . $line . "\n",
        FILE_APPEND | LOCK_EX
    );
}
