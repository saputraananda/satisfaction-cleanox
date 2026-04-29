<?php
// Salin file ini menjadi db.php dan isi dengan kredensial Anda
// cp config/db.example.php config/db.php

define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'your_db_name');

function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        error_log('DB connect error: ' . $conn->connect_error);
        die('Koneksi database gagal. Silakan coba beberapa saat lagi.');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
