<?php
require_once 'auth.php';

$user = authUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

$deviceId = $_POST['device_id'] ?? null;
if (!$deviceId) {
    die('device_id required');
}

/** проверяем, что устройство принадлежит юзеру */
$link = R::findOne(
    'userdevice',
    ' user_id = ? AND device_id = ? ',
    [$user->id, $deviceId]
);

if (!$link) {
    die('Access denied');
}

$device = R::load('device', $deviceId);

/** удаляем */
R::trash($link);

header('Location: index.php');
exit;
