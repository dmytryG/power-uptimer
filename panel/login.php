<?php
require_once 'db.php';
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = R::findOne('user', ' email = ? ', [$email]);

    if (!$user || !password_verify($password, $user->password)) {
        die('Password invalid');
    }

    echo $user;

    $tokenValue = bin2hex(random_bytes(32));

    $token = R::dispense('usertoken');
    $token->token = $tokenValue;
    $token->user = $user;
    $token->created_at = date('Y-m-d H:i:s');
    $token->expires_at = date('Y-m-d H:i:s', time() + 60 * 60 * 24);

    R::store($token);

    setcookie('AUTH_TOKEN', $tokenValue, [
        'expires' => time() + 60 * 60 * 24,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    header('Location: index.php');
    exit;
}
?>

<body>
<div class="content-handler-box">
    <div class="content-box">
        <h2>Login</h2>
        <div class="menu-and-content-view">
            <!--            Left side-->
            <div class="vertical-list">
                <a href="register.php">➕ To registration</a>
            </div>
            <!--            Right side-->
            <div>
                <form method="post" class="vertical-list">
                    <input name="email" placeholder="Email">
                    <input name="password" type="password" placeholder="Password">
                    <button type="submit">Login</button>
                </form>
            </div>
    </div>
</div>
</body>

