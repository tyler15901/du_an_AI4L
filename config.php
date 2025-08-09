<?php
// Cho phép override qua biến môi trường (Laragon vẫn có thể để trống mật khẩu)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'duan_ai');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Kết nối DB thất bại: " . $e->getMessage());
}

// API Key OpenAI (có thể đặt trong biến môi trường OPENAI_API_KEY)
define('API_KEY_OPENAI', getenv('OPENAI_API_KEY') ?: '');
?>