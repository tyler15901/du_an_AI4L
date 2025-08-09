<?php
// config.php
// Cấu hình DB
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'fpt_recommender');
define('DB_USER', 'root');
define('DB_PASS', ''); // đổi theo XAMPP/MAMP

// Tùy chọn sử dụng API
// Nếu bạn muốn gọi OpenAI (internet), set USE_LOCAL_API = false và đặt OPENAI_API_KEY
// Nếu bạn muốn chạy hoàn toàn local (không cần internet), set USE_LOCAL_API = true
define('USE_LOCAL_API', true);
define('OPENAI_API_KEY', 'sk-REPLACE_WITH_YOUR_KEY'); // nếu có

function pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
