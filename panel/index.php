<?php
require_once 'auth.php';

$user = authUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$devices = R::getAll(
    '
    SELECT d.*
    FROM device d
    JOIN userdevice ud ON ud.device_id = d.id
    WHERE ud.user_id = ?
    ORDER BY d.created_at DESC
    ',
    [$user->id]
);
?>

<h1>Привет, <?= htmlspecialchars($user->email) ?></h1>
<a href="logout.php">Logout</a>
<h2>Мои устройства</h2>

<a href="device_add.php">➕ Создать устройство</a>
<a href="device_add_existing.php">➕ Подлинковать устройство</a>

<table border="1" cellpadding="6">
    <tr>
        <th>Название</th>
        <th>Статус</th>
        <th>Последний онлайн</th>
        <th>Токен</th>
        <th></th>
    </tr>

    <?php foreach ($devices as $d): ?>
        <?php
        $lastSeen = strtotime($d['last_seen_at']);
        $isOnline = (time() - $lastSeen) < 600;
        ?>
        <tr>
            <td><?= htmlspecialchars($d['name']) ?></td>
            <td style="text-align:center">
                <?= $isOnline ? '✅' : '❌' ?>
            </td>
            <td><?= htmlspecialchars($d['last_seen_at']) ?></td>
            <td><code><?= htmlspecialchars($d['token']) ?></code></td>
            <td>
                <form method="post" action="device_delete.php" onsubmit="return confirm('Удалить устройство?');">
                    <input type="hidden" name="device_id" value="<?= htmlspecialchars($d['id']) ?>">
                    <button type="submit">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

