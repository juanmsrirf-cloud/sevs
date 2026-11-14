<?php
// diagnostico rapido para cpanel. abrilo en el navegador:
//   https://tu-dominio/api/diag.php?k=ollie
// borralo despues de testear.

if (($_GET['k'] ?? '') !== 'ollie') {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/config.php';
require __DIR__ . '/tg.php';

$stateDir = __DIR__ . '/state';

echo "== entorno ==\n";
echo "php          : " . PHP_VERSION . "\n";
echo "sapi         : " . PHP_SAPI . "\n";
echo "docroot      : " . ($_SERVER['DOCUMENT_ROOT'] ?? '-') . "\n";
echo "__DIR__      : " . __DIR__ . "\n";
echo "open_basedir : " . (ini_get('open_basedir') ?: '(none)') . "\n";
echo "post_max     : " . ini_get('post_max_size') . "\n";
echo "upload_max   : " . ini_get('upload_max_filesize') . "\n";
echo "display_err  : " . ini_get('display_errors') . "\n";
echo "disable_fns  : " . (ini_get('disable_functions') ?: '(none)') . "\n";
echo "allow_url_fp : " . ini_get('allow_url_fopen') . "\n";
echo "\n== extensiones ==\n";
echo "curl         : " . (extension_loaded('curl') ? 'ok' : 'FALTA') . "\n";
echo "openssl      : " . (extension_loaded('openssl') ? 'ok' : 'FALTA') . "\n";
echo "json         : " . (extension_loaded('json') ? 'ok' : 'FALTA') . "\n";
echo "\n== filesystem ==\n";
echo "state exists : " . (is_dir($stateDir) ? 'yes' : 'no') . "\n";
echo "state writ.  : " . (is_dir($stateDir) && is_writable($stateDir) ? 'yes' : 'NO') . "\n";
echo "api writ.    : " . (is_writable(__DIR__) ? 'yes' : 'no') . "\n";

echo "\n== test telegram ==\n";
$ok = sendTg('diag ping ' . date('c'), $key, $id);
echo "sendTg       : " . ($ok ? 'ok' : 'FAIL (ver state/tg.log)') . "\n";

echo "\n== ultimos logs ==\n";
foreach (['tg.log', 'php.log'] as $f) {
    $p = $stateDir . '/' . $f;
    echo "-- {$f} --\n";
    if (is_file($p)) {
        $lines = @file($p, FILE_IGNORE_NEW_LINES);
        if ($lines) {
            foreach (array_slice($lines, -15) as $l) echo $l . "\n";
        } else {
            echo "(vacio)\n";
        }
    } else {
        echo "(no existe)\n";
    }
}
