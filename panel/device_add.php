<?php
require_once 'auth.php';
require_once 'header.php';

$user = authUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (!$name) {
        die('Name is required');
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

<body>
<div class="popup-handler-box">
    <div class="popup-box">
        <h2>Create device</h2>
            <form method="post" class="vertical-list">
                <input name="name" placeholder="Device name">
                <button>Create</button>
            </form>
    </div>
</div>
</body>