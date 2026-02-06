<?php
require_once 'auth.php';
require_once 'header.php';

$user = authUser();
if (!$user) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token'] ?? '');

    if (!$token) {
        die('Token is required');
    }

    $device = R::findOne('device', ' token = ? ', [$token]);
    if (!$device->id) {
        die('Device not found');
    }

    $alreadyLinked = R::findOne('userdevice', ' device_id = ? AND user_id = ?', [$device->id, $user->id]);

    if (!!$alreadyLinked->id) {
        die('Device already linked');
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

<body>
<div class="popup-handler-box">
    <div class="popup-box">
        <h2>Link existing device</h2>
        <form method="post" class="vertical-list">
            <input name="token" placeholder="Device token">
            <button>Link</button>
        </form>
    </div>
</div>
</body>
