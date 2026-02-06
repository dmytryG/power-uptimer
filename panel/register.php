<?php
require_once 'db.php';
require_once 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        die('Email and password are required');
    }

    if (R::findOne('user', ' email = ? ', [$email])) {
        die('User already exist');
    }

    $user = R::dispense('user');
    $user->email = $email;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    $user->created_at = date('Y-m-d H:i:s');

    R::store($user);

    echo 'Success';
    header('Location: login.php');
    exit;
}
?>

<body>
<div class="content-handler-box">
    <div class="content-box">
        <h2>Registration</h2>
        <div class="menu-and-content-view">
            <!--            Left side-->
            <div class="vertical-list">
                <a href="login.php">➕ To login</a>
            </div>
            <!--            Right side-->
            <div>
                <form method="post" class="vertical-list">
                    <input name="email" placeholder="Email">
                    <input name="password" type="password" placeholder="Password">
                    <button type="submit">Register</button>
                </form>
            </div>
        </div>
    </div>
</body>
