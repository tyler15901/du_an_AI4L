<?php
// Load environment variables from .env if present
require_once __DIR__ . '/env.php';
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
    // Trả JSON để client không lỗi parse
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'message' => 'Kết nối DB thất bại', 'error' => $e->getMessage()]);
    exit;
}

// API Key OpenAI (đặt trong biến môi trường OPENAI_API_KEY)
define('API_KEY_OPENAI', getenv('OPENAI_API_KEY') ?: '');

// Nếu dùng Cursor API/proxy, cấu hình qua biến môi trường sau (không hardcode key):
// CURSOR_API_URL: ví dụ https://api.cursor.dev/v1 (hoặc proxy riêng)
// CURSOR_API_KEY: Bearer token để gọi API của Cursor
// CURSOR_MODEL  : tên model (mặc định gpt-4o-mini)
define('CURSOR_API_URL', getenv('CURSOR_API_URL') ?: '');
define('CURSOR_API_KEY', getenv('CURSOR_API_KEY') ?: '');
define('CURSOR_MODEL', getenv('CURSOR_MODEL') ?: 'gpt-4o-mini');
?>