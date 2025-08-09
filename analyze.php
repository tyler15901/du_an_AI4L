<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php'); exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? null);
$preferences = trim($_POST['preferences'] ?? '');
$scores = trim($_POST['scores'] ?? '');
$personality = trim($_POST['personality'] ?? '');

$input = [
    'name' => $name,
    'email' => $email,
    'preferences' => $preferences,
    'scores' => $scores,
    'personality' => $personality
];

$prompt = "Bạn là một chuyên gia tư vấn hướng nghiệp cho học sinh đại học. Dựa vào thông tin dưới đây, đề xuất 3 ngành học phù hợp trong danh sách majors có sẵn (IT, BA, DS, DSA). Nêu lý do ngắn cho từng ngành, các môn nên học, kỹ năng cần cải thiện và mức độ phù hợp.\n\nThông tin học sinh:\n" . json_encode($input, JSON_UNESCAPED_UNICODE);

$result_text = '';
$suggested_codes = [];

if (USE_LOCAL_API) {
    // Gọi local API giả lập
    $ch = curl_init();
    $payload = json_encode(['prompt' => $prompt], JSON_UNESCAPED_UNICODE);
    curl_setopt($ch, CURLOPT_URL, (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http') . '://'. $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/local_ai.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $resp = curl_exec($ch);
    if ($resp === false) {
        $result_text = 'Lỗi khi gọi local AI: '.curl_error($ch);
    } else {
        $respObj = json_decode($resp, true);
        $result_text = $respObj['text'] ?? $resp;
        $suggested_codes = $respObj['codes'] ?? [];
    }
    curl_close($ch);
} else {
    $apiKey = OPENAI_API_KEY;
    if (empty($apiKey)) {
        $result_text = "OPENAI_API_KEY chưa cấu hình.";
    } else {
        $data = [
            "model" => "gpt-3.5-turbo",
            "messages" => [
                ["role" => "system", "content" => "Bạn là một chuyên gia tư vấn hướng nghiệp cho học sinh."],
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 600,
            "temperature" => 0.7
        ];
        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apiKey
        ]);
        $resp = curl_exec($ch);
        if ($resp === false) {
            $result_text = 'Lỗi khi gọi OpenAI: ' . curl_error($ch);
        } else {
            $j = json_decode($resp, true);
            $result_text = $j['choices'][0]['message']['content'] ?? json_encode($j);
            preg_match_all('/\b(IT|BA|DS|DSA)\b/i', $result_text, $m);
            $suggested_codes = array_unique(array_map('strtoupper', $m[0] ?? []));
        }
        curl_close($ch);
    }
}

$pdo = pdo();
$userid = null;
if ($email || $name) {
    $stmt = $pdo->prepare("INSERT INTO users (name,email) VALUES (:name,:email)");
    $stmt->execute(['name'=>$name, 'email'=>$email]);
    $userid = $pdo->lastInsertId();
}

$stmt = $pdo->prepare("INSERT INTO recommendations (user_id, input_json, result_text, suggested_major_codes) VALUES (:user_id, :input_json, :result_text, :codes)");
$stmt->execute([
    'user_id' => $userid,
    'input_json' => json_encode($input, JSON_UNESCAPED_UNICODE),
    'result_text' => $result_text,
    'codes' => implode(',', $suggested_codes)
]);

$recid = $pdo->lastInsertId();
header("Location: result.php?id=".$recid);
exit;
