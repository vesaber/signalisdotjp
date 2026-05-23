<?php
$audioDir = __DIR__ . '/audio';
$supportedTypes = [
    'flac' => 'audio/flac',
    'mp3' => 'audio/mpeg',
];
$fileName = $_GET['file'] ?? '';
$fileName = str_replace("\0", '', $fileName);
$fileName = basename($fileName);
$filePath = $audioDir . '/' . $fileName;
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

if ($fileName === '' || !is_file($filePath) || !isset($supportedTypes[$extension])) {
    http_response_code(404);
    exit;
}

$fileSize = filesize($filePath);
$start = 0;
$end = $fileSize - 1;
$statusCode = 200;

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
    $statusCode = 206;

    if ($matches[1] === '' && $matches[2] !== '') {
        $suffixLength = min((int) $matches[2], $fileSize);
        $start = $fileSize - $suffixLength;
    } else {
        $start = (int) $matches[1];
    }

    if ($matches[2] !== '') {
        $end = min((int) $matches[2], $fileSize - 1);
    }

    if ($start > $end || $start < 0 || $start >= $fileSize) {
        header('Content-Range: bytes */' . $fileSize);
        http_response_code(416);
        exit;
    }
}

$length = $end - $start + 1;

http_response_code($statusCode);
header('Content-Type: ' . $supportedTypes[$extension]);
header('Accept-Ranges: bytes');
header('Content-Length: ' . $length);
header('Content-Disposition: inline; filename="' . addcslashes($fileName, "\\\"") . '"');

if ($statusCode === 206) {
    header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
}

if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    exit;
}

$handle = fopen($filePath, 'rb');

if ($handle === false) {
    http_response_code(500);
    exit;
}

fseek($handle, $start);
$remaining = $length;

while ($remaining > 0 && !feof($handle)) {
    $chunkSize = min(8192, $remaining);
    echo fread($handle, $chunkSize);
    flush();
    $remaining -= $chunkSize;
}

fclose($handle);
