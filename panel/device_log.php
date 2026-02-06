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

$days = [];
$now = new DateTime('now');
$now->setTime(23, 59, 59);

// инициализируем последние 7 дней
for ($i = 0; $i < 7; $i++) {
    $day = clone $now;
    $day->modify("-$i day");
    $key = $day->format('Y-m-d');
    $days[$key] = 0; // секунды
}

$fromDate = (clone $now)->modify('-6 day')->setTime(0, 0, 0);

$uptimes = R::getAll(
    '
    SELECT started_at, ended_at
    FROM deviceuptime
    WHERE device_id = ?
      AND ended_at >= ?
    ',
    [$device->id, $fromDate->format('Y-m-d H:i:s')]
);

foreach ($uptimes as $u) {
    $start = new DateTime($u['started_at']);
    $end   = new DateTime($u['ended_at']);

    foreach ($days as $day => $_) {
        $dayStart = new DateTime($day . ' 00:00:00');
        $dayEnd   = new DateTime($day . ' 23:59:59');

        $realStart = max($start, $dayStart);
        $realEnd   = min($end, $dayEnd);

        if ($realStart < $realEnd) {
            $days[$day] += $realEnd->getTimestamp() - $realStart->getTimestamp();
        }
    }
}

// для графика: часы онлайн
$chartLabels = array_reverse(array_keys($days));
$chartData = array_reverse(
    array_map(fn($s) => round($s / 3600, 2), $days)
);
?>

<h2><a href="index.php">Домой</a></h2>
<h2>Лог устройства <?=$device->name ?></h2>

<h3>Онлайн за последние 7 дней (часы)</h3>

<canvas id="uptimeChart" height="120"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('uptimeChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chartLabels) ?>,
            datasets: [{
                label: 'Часов онлайн',
                data: <?= json_encode($chartData) ?>,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Часы'
                    }
                }
            }
        }
    });
</script>

<?php
    function percent($uptimeHours, $totalHours): float {
        if ($totalHours <= 0) return 0;
        return round(($uptimeHours / $totalHours) * 100, 1);
    }

    $chartDataArrayed = [];
    foreach ($chartData as $v) {
        $chartDataArrayed[] = $v;
    }
    $chartDataArrayed = array_reverse($chartDataArrayed);

    $last3DaysHours = array_sum(array_slice($chartDataArrayed, 0, 3));
    $weekHours = array_sum(array_slice($chartDataArrayed, 0, 7));

//    var_dump($chartDataArrayed);
?>


<table border="1" cellpadding="6">
    <tr>
        <th>Период</th>
        <th>Аптайм %</th>
        <th>Аптайм (часы)</th>
        <th>Даунтайм (часы)</th>
    </tr>

    <tr>
        <td>Сегодня</td>
        <td><?= percent($chartDataArrayed[0], 24) ?>%</td>
        <td><?= $chartDataArrayed[0] ?></td>
        <td><?= 24 - $chartDataArrayed[0] ?></td>
    </tr>

    <tr>
        <td>Вчера</td>
        <td><?= percent($chartDataArrayed[1], 24) ?>%</td>
        <td><?= $chartDataArrayed[1] ?></td>
        <td><?= 24 - $chartDataArrayed[1] ?></td>
    </tr>

    <tr>
        <td>3 дня</td>
        <td><?= percent($last3DaysHours, 72) ?>%</td>
        <td><?= $last3DaysHours ?></td>
        <td><?= 72 - $last3DaysHours ?></td>
    </tr>

    <tr>
        <td>Неделя</td>
        <td><?= percent($weekHours, 168) ?>%</td>
        <td><?= $weekHours ?></td>
        <td><?= 168 - $weekHours ?></td>
    </tr>
</table>

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

