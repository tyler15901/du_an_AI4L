<?php
// config.php

require_once __DIR__ . '/env.php';

// Ưu tiên lấy từ biến môi trường hoặc .env; fallback về giá trị cũ
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'du_an_ai');  // Đổi tên DB của bạn
define('DB_USER', getenv('DB_USER') ?: 'root');      // User MySQL
define('DB_PASS', getenv('DB_PASS') ?: '');          // Mật khẩu MySQL

// OPENAI_API_KEY được nạp trong env.php nếu có
?>
