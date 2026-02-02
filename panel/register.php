<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        die('Email и пароль обязательны');
    }

    if (R::findOne('user', ' email = ? ', [$email])) {
        die('Пользователь уже существует');
    }

    $user = R::dispense('user');
    $user->email = $email;
    $user->password = password_hash($password, PASSWORD_DEFAULT);
    $user->created_at = date('Y-m-d H:i:s');

    R::store($user);

    echo 'Регистрация успешна';
}
?>

<body>
<form method="post">
    <input name="email" placeholder="Email">
    <input name="password" type="password" placeholder="Password">
    <button type="submit">Register</button>
</form>
<a href="login.php">To login</a>
</body>


