<?php
require_once 'rb-mysql.php';
require_once 'config.php';


R::setup(
    Config::$db_host,
    Config::$db_username,
    Config::$db_password
);

R::freeze(true); // true на проде
