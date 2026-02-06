<?php
require_once 'db.php';
require_once 'config.php';

function registerDevicePing($token): string
{
    if (!$token) {
        http_response_code(400);
        return 'token required';
        exit;
    }

    $device = R::findOne('device', ' token = ? ', [$token]);
    if (!$device->id) {
        http_response_code(401);
        return 'invalid token';
        exit;
    }

    $now = time();
    $nowDt = date('Y-m-d H:i:s');

    $device->last_seen_at = $nowDt;
    R::store($device);

    $uptime = R::findOne(
        'deviceuptime',
        ' device_id = ? ORDER BY ended_at DESC ',
        [$device->id]
    );

    if ($uptime && $uptime->id) {
        $endedTs = strtotime($uptime->ended_at);

        if (($now - $endedTs) < Config::$threshhold) {
            // продолжаем существующий аптайм
            $uptime->ended_at = $nowDt;
            R::store($uptime);
        } else {
            // разрыв больше threshold → новый аптайм
            $uptime = R::dispense('deviceuptime');
            $uptime->device = $device;
            $uptime->started_at = $nowDt;
            $uptime->ended_at   = $nowDt;
            R::store($uptime);
        }
    } else {
        // аптаймов ещё не было
        $uptime = R::dispense('deviceuptime');
        $uptime->device = $device;
        $uptime->started_at = $nowDt;
        $uptime->ended_at   = $nowDt;
        R::store($uptime);
    }

    $uptimeIds = R::getCol(
        'SELECT id FROM deviceuptime 
         WHERE device_id = ?
         ORDER BY ended_at DESC
         LIMIT 100, 1000',
        [$device->id]
    );

    if ($uptimeIds) {
        R::trashAll(R::loadAll('deviceuptime', $uptimeIds));
    }

    return 'ok';
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$token = $data['token'] ?? null;

echo registerDevicePing($token);
