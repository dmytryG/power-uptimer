<?php
require_once 'rb-mysql.php';


R::setup(
    'mysql:host=localhost;dbname=power-uptimer;charset=utf8mb4',
    'root',
    ''
);

R::freeze(true); // true на проде
