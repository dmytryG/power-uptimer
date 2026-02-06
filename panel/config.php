<?php
// local
//    class Config {

//    }

// prod
    class Config {
        static $threshhold = 600; // 10 minutes
        // local
        static $db_host = 'mysql:host=localhost;dbname=power-uptimer;charset=utf8mb4';
        static $db_password = '';
        static $db_username = 'root';
        // prod

    }
?>