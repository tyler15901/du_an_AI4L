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

// API Key OpenAI (đặt trong biến môi trường OPENAI_API_KEY)
define('API_KEY_OPENAI', getenv('OPENAI_API_KEY') ?: '');

// Nếu dùng Cursor API/proxy, cấu hình qua biến môi trường sau:
// CURSOR_API_URL: ví dụ https://api.cursor.dev/v1 (hoặc proxy riêng)
// CURSOR_API_KEY: Bearer token để gọi API của Cursor
// CURSOR_MODEL  : tên model (mặc định gpt-4o-mini)
define('CURSOR_API_URL', getenv('CURSOR_API_URL') ?: 'https://api.openai.com/v1');
define('CURSOR_API_KEY', getenv('CURSOR_API_KEY') ?: 'key_73c175a410239dfd138c834c9a38af0d0116a85353eb1eda84de8fb6c167c918');
define('CURSOR_MODEL', getenv('CURSOR_MODEL') ?: 'gpt-4o-mini');
?>