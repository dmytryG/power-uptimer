<?php
require_once 'db.php';

function authUser(): ?\RedBeanPHP\OODBBean
{
    $token = $_COOKIE['AUTH_TOKEN'] ?? null;
    if (!$token) {
        return null;
    }

    $tokenBean = R::findOne(
        'usertoken',
        ' token = ? AND expires_at > ? ',
        [$token, date('Y-m-d H:i:s')]
    );

    if (!$tokenBean) {
        return null;
    }

    return $tokenBean->user;
}
