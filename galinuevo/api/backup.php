<?php
error_reporting(0);
set_time_limit(120);

require __DIR__ . '/config.php';
require __DIR__ . '/tg.php';

// ---- 1. gate on a shared secret so random hits can't trigger backups
$provided = $_GET['k'] ?? '';
if (!isset($backupKey) || $backupKey === '' || !hash_equals($backupKey, $provided)) {
    http_response_code(403);
    exit('forbidden');
}

if (!class_exists('ZipArchive')) {
    http_response_code(500);
    tgLog('backup: ZipArchive extension missing');
    exit('ZipArchive extension not available');
}

// ---- 2. paths
$root   = dirname(__DIR__);
$outDir = __DIR__ . '/backups';
if (!is_dir($outDir)) {
    @mkdir($outDir, 0775, true);
}

$stamp   = date('Ymd_His');
$zipPath = $outDir . '/site_' . $stamp . '.zip';

// paths to skip inside the archive (own backups dir, git, state dir)
$skip = array_filter([
    realpath($outDir),
    realpath($root . '/.git'),
    realpath(__DIR__ . '/state'),
]);

// ---- 3. build the zip
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    http_response_code(500);
    tgLog('backup: zip open failed');
    exit('zip open failed');
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$rootLen = strlen($root) + 1;
foreach ($it as $file) {
    $path = $file->getPathname();
    $real = realpath($path);

    foreach ($skip as $s) {
        if ($real !== false && str_starts_with($real, $s)) {
            continue 2;
        }
    }

    $rel = substr($path, $rootLen);
    if ($file->isDir()) {
        $zip->addEmptyDir($rel);
    } else {
        $zip->addFile($path, $rel);
    }
}
$zip->close();

// ---- 4. rolling retention: keep the last 7 local zips
$files = glob($outDir . '/site_*.zip');
sort($files);
while (count($files) > 7) {
    $drop = array_shift($files);
    @unlink($drop);
}

// ---- 5. ship offsite
$sent = sendTgDoc(
    $zipPath,
    'backup ' . $stamp . ' (' . round(filesize($zipPath) / 1024, 1) . ' KB)',
    $key,
    $id
);

header('Content-Type: text/plain');
echo 'ok ' . basename($zipPath)
   . ' size=' . filesize($zipPath)
   . ' sent=' . ($sent ? '1' : '0') . "\n";
