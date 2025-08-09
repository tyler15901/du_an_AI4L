<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tu_van_nganh_hoc');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch (PDOException $e) {
    die("Kết nối DB thất bại: " . $e->getMessage());
}

// API Key OpenAI (thay bằng key thực)
define('API_KEY_OPENAI', 'sk-your-openai-key-here');
?>