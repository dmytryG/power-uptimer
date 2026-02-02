<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$token = $data['token'] ?? null;
if (!$token) {
    http_response_code(400);
    echo 'token required';
    exit;
}

$device = R::findOne('device', ' token = ? ', [$token]);
if (!$device->id) {
    http_response_code(401);
    echo 'invalid token';
    exit;
}

$device->last_seen_at = date('Y-m-d H:i:s');
R::store($device);

echo 'ok';
