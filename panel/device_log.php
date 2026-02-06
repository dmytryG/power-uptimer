<?php
require_once 'auth.php';
require_once 'config.php';

$user = authUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$deviceId = $_GET['id'] ?? null;
if (!$deviceId) {
    die('device_id required');
}

$link = R::findOne(
    'userdevice',
    ' user_id = ? AND device_id = ? ',
    [$user->id, $deviceId]
);

if (!$link) {
    die('Access denied');
}

$device = R::load('device', $deviceId);

$logs = R::getAll(
    '
    SELECT l.*
    FROM deviceuptime l
    WHERE l.device_id = ?
    ORDER BY l.started_at DESC
    ',
    [$device->id]
);
?>

<h2><a href="index.php">Домой</a></h2>
<h2>Лог устройства <?=$device->name ?></h2>

<table border="1" cellpadding="6">
    <tr>
        <th>#</th>
        <th>Период начало</th>
        <th>Период конец</th>
    </tr>

    <?php foreach ($logs as $i => $l): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($l['started_at']) ?></td>
            <td><?= htmlspecialchars($l['ended_at']) ?></td>
        </tr>
    <?php endforeach; ?>

</table>

