<?php
require_once 'auth.php';
$user = authUser();
?>
<head>
    <link href="style.css" rel="stylesheet">
    <title>Power uptimer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:ital,opsz,wght@0,17..18,400..700;1,17..18,400..700&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
</head>
<div class="header-box">
    <span class="large-text"><a href="index.php">Power uptimer</a></span>
    <span>Powered by ESP32</span>
    <?php if ($user): ?>
        <div>
            <a href="logout.php">Logout</a>
            <span><?= htmlspecialchars($user->email) ?></span>
        </div>
    <?php endif; ?>
</div>
