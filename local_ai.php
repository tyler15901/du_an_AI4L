<?php
header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$prompt = $data['prompt'] ?? '';

$responseText = "";
$codes = [];

$lower = mb_strtolower($prompt, 'UTF-8');

if (mb_strpos($lower, 'toán') !== false || mb_strpos($lower, 'lý') !== false || mb_strpos($lower, 'kỹ thuật') !== false || mb_strpos($lower, 'coding') !== false) {
    $codes[] = 'IT';
}
if (mb_strpos($lower, 'marketing') !== false || mb_strpos($lower, 'kinh doanh') !== false || mb_strpos($lower, 'quản trị') !== false) {
    $codes[] = 'BA';
}
if (mb_strpos($lower, 'phân tích') !== false || mb_strpos($lower, 'dữ liệu') !== false || mb_strpos($lower, 'statistics') !== false) {
    $codes[] = 'DS';
}
if (mb_strpos($lower, 'sáng tạo') !== false || mb_strpos($lower, 'thiết kế') !== false || mb_strpos($lower, 'ux') !== false) {
    $codes[] = 'DSA';
}

if (empty($codes)) {
    $codes = ['IT', 'BA'];
}

$map = [
    'IT' => "CANDIDATE phù hợp ngành CNTT (IT): Thích lập trình, tư duy logic. Nên học: Lập trình, CSDL, cấu trúc dữ liệu.",
    'BA' => "Ngành Kinh doanh & Quản trị (BA): Thích giao tiếp, sáng tạo bán hàng. Nên học: Marketing, Tài chính cơ bản.",
    'DS' => "Ngành Khoa học dữ liệu (DS): Thích phân tích, toán, thống kê. Nên học: Python, xử lý dữ liệu, thống kê.",
    'DSA' => "Ngành Thiết kế (DSA): Thích sáng tạo, mỹ thuật. Nên học: Thiết kế đồ hoạ, UX/UI, portfolio."
];

$textPieces = [];
foreach ($codes as $c) {
    $t = str_replace('CANDIDATE', 'Học sinh', $map[$c]);
    $textPieces[] = $t;
}

$responseText = "Gợi ý ngành:\n" . implode("\n\n", $textPieces) . "\n\nLý do: dựa trên thông tin bạn cung cấp.";

echo json_encode(['text' => $responseText, 'codes' => $codes], JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
