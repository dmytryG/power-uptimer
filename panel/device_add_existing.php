<?php
require_once 'auth.php';

$user = authUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');

    if (!$token) {
        die('Token обязательно');
    }

    $device = R::findOne('device', ' token = ? ', [$token]);
    if (!$device->id) {
        die('Устройство не найдено');
    }

    $alreadyLinked = R::findOne('userdevice', ' device_id = ? AND user_id = ?', [$device->id, $user->id]);

    if (!!$alreadyLinked->id) {
        die('Устройство уже прилинковано');
    }

    /** PIVOT */
    $link = R::dispense('userdevice');
    $link->user = $user;
    $link->device = $device;
    $link->created_at = date('Y-m-d H:i:s');
    R::store($link);

    header('Location: index.php');
    exit;
}
?>

<h1>Подлинковать устройство</h1>

<form method="post">
    <input name="token" placeholder="Токен устройства">
    <button>Добавить</button>
</form>
