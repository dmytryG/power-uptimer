<?php
require_once 'auth.php';
require_once 'config.php';
require_once 'js-utils.php';

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

<?php

require_once 'header.php';

?>
<div class="content-handler-box">
    <div class="content-box">
        <h2>My devices</h2>
        <div class="menu-and-content-view">
<!--            Left side-->
            <div class="vertical-list">
                <a href="device_add.php">➕ Create new device</a>
                <a href="device_add_existing.php">➕ Link existing device</a>
            </div>
<!--            Right side-->
            <div>
                <table border="1" cellpadding="6">
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Last online</th>
                        <th>Token</th>
                        <th>Actions</th>
                    </tr>

                    <?php foreach ($devices as $d): ?>
                        <?php
                        $lastSeen = strtotime($d['last_seen_at']);
                        $isOnline = (time() - $lastSeen) < Config::$threshhold;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($d['name']) ?></td>
                            <td style="text-align:center">
                                <?= $isOnline ? '✅' : '❌' ?>
                            </td>
                            <td><?= htmlspecialchars($d['last_seen_at']) ?></td>
                            <td><code class="copy-token"><?= htmlspecialchars($d['token']) ?></code></td>
                            <td class="vertical-list-tight">
                                <form method="post" action="device_delete.php" onsubmit="return confirm('Delete device?');">
                                    <input type="hidden" name="device_id" value="<?= htmlspecialchars($d['id']) ?>">
                                    <button type="submit">Delete</button>
                                </form>
                                <a href="device_log.php?id=<?=$d['id'] ?>">Uptime log</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</div>







