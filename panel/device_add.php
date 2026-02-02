<?php
require_once 'auth.php';

$user = authUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (!$name) {
        die('Название обязательно');
    }

    $token = bin2hex(random_bytes(32)); // 🔑 device id

    /** DEVICE */
    $device = R::dispense('device');
    $device->token = $token;
    $device->name = $name;
    $device->last_seen_at = '1970-01-01 00:00:00';
    $device->created_at = date('Y-m-d H:i:s');
    R::store($device);

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

<h1>Добавить устройство</h1>

<form method="post">
    <input name="name" placeholder="Название устройства">
    <button>Создать</button>
</form>
