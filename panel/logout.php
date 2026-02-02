<?php
require_once 'db.php';

$tokenValue = $_COOKIE['AUTH_TOKEN'] ?? null;

if ($tokenValue) {
    $token = R::findOne('usertoken', ' token = ? ', [$tokenValue]);
    if ($token) {
        R::trash($token);
    }
}

setcookie('AUTH_TOKEN', '', time() - 3600, '/');
header('Location: login.php');
exit;
