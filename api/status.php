<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$serverId = (int)($_GET['id'] ?? 0);

if ($serverId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid server ID']);
    exit;
}

$stmt = db()->prepare('SELECT join_code FROM servers WHERE id = ? AND is_approved = 1');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server) {
    http_response_code(404);
    echo json_encode(['error' => 'Server not found']);
    exit;
}

$joinCode = $server['join_code'];
$apiUrl = 'https://frontend.cfx-services.net/api/servers/single/' . urlencode($joinCode);

$response = null;

// Try file_get_contents with stream context first
$opts = ['http' => [
    'method' => 'GET',
    'header' => "Accept: application/json\r\nUser-Agent: RedMServers/1.0\r\n",
    'timeout' => 10,
    'ignore_errors' => true,
]];
$response = @file_get_contents($apiUrl, false, stream_context_create($opts));

if ($response !== false) {
    $data = json_decode($response, true);
    if ($data && isset($data['Data'])) {
$allowlisted = ($data['Data']['vars']['sv_appearAllowlisted'] ?? 'false') === 'true';
        echo json_encode([
            'online'       => true,
            'join_code'    => $joinCode,
            'hostname'     => $data['Data']['hostname'] ?? '',
            'players'      => (int)($data['Data']['clients'] ?? 0),
            'max_players'  => (int)($data['Data']['sv_maxclients'] ?? 0),
            'gametype'     => $data['Data']['gametype'] ?? '',
            'map'          => $data['Data']['mapname'] ?? '',
            'join_url'     => 'https://cfx.re/join/' . $joinCode,
            'allowlisted'  => $allowlisted,
        ]);
        exit;
    }
}

// Try fsockopen as fallback
$host = 'frontend.cfx-services.net';
$fp = @fsockopen('tls://' . $host, 443, $errno, $errstr, 8);
if ($fp) {
    $out = "GET /api/servers/single/" . urlencode($joinCode) . " HTTP/1.1\r\n";
    $out .= "Host: $host\r\n";
    $out .= "Accept: application/json\r\n";
    $out .= "User-Agent: RedMServers/1.0\r\n";
    $out .= "Connection: close\r\n\r\n";
    fwrite($fp, $out);
    $raw = '';
    while (!feof($fp)) {
        $raw .= fgets($fp, 4096);
    }
    fclose($fp);
    $parts = explode("\r\n\r\n", $raw, 2);
    if (isset($parts[1])) {
        $data = json_decode($parts[1], true);
        if ($data && isset($data['Data'])) {
            $allowlisted = ($data['Data']['vars']['sv_appearAllowlisted'] ?? 'false') === 'true';
            echo json_encode([
                'online'       => true,
                'join_code'    => $joinCode,
                'hostname'     => $data['Data']['hostname'] ?? '',
                'players'      => (int)($data['Data']['clients'] ?? 0),
                'max_players'  => (int)($data['Data']['sv_maxclients'] ?? 0),
                'gametype'     => $data['Data']['gametype'] ?? '',
                'map'          => $data['Data']['mapname'] ?? '',
                'join_url'     => 'https://cfx.re/join/' . $joinCode,
                'allowlisted'  => $allowlisted,
            ]);
            exit;
        }
    }
}

echo json_encode([
    'online'    => false,
    'join_code' => $joinCode,
    'join_url'  => 'https://cfx.re/join/' . $joinCode,
]);
