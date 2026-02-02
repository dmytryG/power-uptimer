<?php
require 'core-3.18.0/flight/Flight.php';
require_once 'db.php'; // Подключаем RedBeanPHP

// Ограничиваем роут только POST запросом
Flight::route('POST /device_ping_api', function() {

    // Flight автоматически парсит JSON, если пришел заголовок Content-Type: application/json
    $request = Flight::request();
    $token = $request->data->token;

    // 1. Проверка наличия токена
    if (empty($token)) {
        Flight::halt(400, 'token required');
    }

    // 2. Поиск устройства через RedBeanPHP
    $device = R::findOne('device', ' token = ? ', [$token]);

    if (!$device) {
        Flight::halt(401, 'invalid token');
    }

    // 3. Обновление времени
    $device->last_seen_at = date('Y-m-d H:i:s');
    R::store($device);

    // 4. Ответ
    // Если хочешь просто текст "ok":
    echo "ok";

    // Если хочешь правильный JSON ответ (лучше для API):
    // Flight::json(['status' => 'ok']);
});

// Обработка случая, если метод не POST или роут не найден
Flight::map('notFound', function(){
    Flight::halt(404, 'Not Found 2');
});

Flight::start();