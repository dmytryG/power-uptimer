<?php
require_once 'db.php';

R::freeze(false);

echo '<pre>DB init started…</pre>';

/**
 * USERS
 */
$user = R::dispense('user');
$user->email = 'init@example.com';
$user->password = password_hash('init', PASSWORD_DEFAULT);
$user->created_at = date('Y-m-d H:i:s');
R::store($user);
R::trash($user);

/**
 * USERTOKEN
 */
$token = R::dispense('usertoken');
$token->token = str_repeat('a', 64);
$token->user = R::dispense('user');
$token->created_at = date('Y-m-d H:i:s');
$token->expires_at = date('Y-m-d H:i:s');
R::store($token);
R::trash($token);

/** DEVICE (id = token) */
$device = R::dispense('device');
$device->token = bin2hex(random_bytes(16));
$device->name = 'Init device';
$device->last_seen_at = '1970-01-01 00:00:00';
$device->created_at = date('Y-m-d H:i:s');
R::store($device);
R::trash($device);

/** PIVOT */
$link = R::dispense('userdevice');
$link->user = $user;
$link->device = $device;
$link->created_at = date('Y-m-d H:i:s');
R::store($link);
R::trash($link);

echo '<pre>DB init done ✅</pre>';
