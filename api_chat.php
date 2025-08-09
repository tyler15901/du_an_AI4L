<?php
require 'config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = pdo();
$raw = file_get_contents('php://input');
$body = json_decode($raw, true) ?? [];
$conversationId = isset($body['conversation_id']) ? (int)$body['conversation_id'] : null;
$message = trim($body['message'] ?? '');

if ($message === '') {
    echo json_encode(['error' => 'empty_message']);
    exit;
}

// Tạo conversation nếu chưa có
if (!$conversationId) {
    $stmt = $pdo->prepare("INSERT INTO conversations (user_id) VALUES (NULL)");
    $stmt->execute();
    $conversationId = (int)$pdo->lastInsertId();
}

// Lưu tin nhắn user
$stmt = $pdo->prepare("INSERT INTO messages (conversation_id, role, content) VALUES (:cid, 'user', :content)");
$stmt->execute(['cid' => $conversationId, 'content' => $message]);

// Build context (lấy 10 tin gần nhất)
$stmt = $pdo->prepare("SELECT role, content FROM messages WHERE conversation_id = :cid ORDER BY id DESC LIMIT 10");
$stmt->execute(['cid' => $conversationId]);
$recent = array_reverse($stmt->fetchAll());

// System prompt
$system = "Bạn là chuyên gia hướng nghiệp. Hỏi sâu để hiểu người dùng, rồi đề xuất 3-4 ngành/nghề phù hợp (ví dụ IT, BA, DS, DSA hoặc ngành khác nếu hợp lý). Với mỗi gợi ý: lý do ngắn, môn nên học, kỹ năng cần rèn, kế hoạch 3-6-12 tháng. Luôn khuyến nghị tìm tư vấn chuyên gia khi thiếu dữ liệu.";

$promptMessages = [ [ 'role' => 'system', 'content' => $system ] ];
foreach ($recent as $m) {
    $promptMessages[] = [ 'role' => $m['role'], 'content' => $m['content'] ];
}

$reply = '';
$codes = [];

if (USE_LOCAL_API) {
    // gọi local_ai.php cho câu trả lời tóm tắt, sau đó tinh chỉnh format
    $ch = curl_init();
    $payload = json_encode(['prompt' => $message], JSON_UNESCAPED_UNICODE);
    $url = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/local_ai.php';
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        $reply = 'Lỗi khi gọi local AI: ' . curl_error($ch);
    } else {
        $obj = json_decode($resp, true);
        $reply = $obj['text'] ?? $resp;
        $codes = $obj['codes'] ?? [];
    }
    curl_close($ch);
} else {
    $apiKey = OPENAI_API_KEY;
    if (empty($apiKey)) {
        $reply = 'OPENAI_API_KEY chưa cấu hình.';
    } else {
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $promptMessages,
            'max_tokens' => 700,
            'temperature' => 0.7,
        ];
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ]
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $reply = 'Lỗi khi gọi OpenAI: ' . curl_error($ch);
        } else {
            $j = json_decode($resp, true);
            $reply = $j['choices'][0]['message']['content'] ?? json_encode($j);
            if (preg_match_all('/\b(IT|BA|DS|DSA)\b/i', $reply, $m)) {
                $codes = array_unique(array_map('strtoupper', $m[0]));
            }
        }
        curl_close($ch);
    }
}

// Lưu trả lời AI
$stmt = $pdo->prepare("INSERT INTO messages (conversation_id, role, content) VALUES (:cid, 'assistant', :content)");
$stmt->execute(['cid' => $conversationId, 'content' => $reply]);

echo json_encode([
    'conversation_id' => $conversationId,
    'reply' => $reply,
    'codes' => $codes,
], JSON_UNESCAPED_UNICODE);


