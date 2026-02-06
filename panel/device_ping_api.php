<?php
require 'core-3.18.0/flight/Flight.php';
require_once 'device_ping.php';

// Ограничиваем роут только POST запросом
Flight::route('POST /device_ping_api', function() {

    // Flight автоматически парсит JSON, если пришел заголовок Content-Type: application/json
    $request = Flight::request();
    $token = $request->data->token;

    echo registerDevicePing($token);
});

// Обработка случая, если метод не POST или роут не найден
Flight::map('notFound', function(){
    Flight::halt(404, 'Not Found 2');
});

Flight::start();