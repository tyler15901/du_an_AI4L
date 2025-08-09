<?php
require 'config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate và sanitize data (demo đơn giản, thêm filter chi tiết hơn)
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $gender = filter_input(INPUT_POST, 'gender', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    // ... Thu thập tất cả field tương tự (interests, values, etc. là array -> json_encode)

    if (!$name || !$age || !$email) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        exit;
    }

    // Chuẩn bị data JSON cho arrays
    $subject_scores = json_encode($_POST['subject_scores'] ?? []);
    $certificates = json_encode($_POST['certificates'] ?? []);
    $interests = json_encode($_POST['interests'] ?? []);
    $values = json_encode($_POST['values'] ?? []);
    $skills = json_encode($_POST['skills'] ?? []);
    $skill_levels = json_encode($_POST['skill_levels'] ?? []); // Giả sử JS thêm field này
    $initial_interests = json_encode($_POST['initial_interests'] ?? []);

    // Insert vào DB
    $stmt = $pdo->prepare("INSERT INTO customers (name, gender, age, email, phone, address, school, grad_year, avg_score, subject_scores, certificates, interests, `values`, interest_desc, skills, skill_levels, experience, goals, initial_interests, source) 
                           VALUES (:name, :gender, :age, :email, :phone, :address, :school, :grad_year, :avg_score, :subject_scores, :certificates, :interests, :values, :interest_desc, :skills, :skill_levels, :experience, :goals, :initial_interests, :source)");
    $stmt->execute([
        'name' => $name,
        'gender' => $gender,
        'age' => $age,
        'email' => $email,
        'phone' => $_POST['phone'] ?? null,
        'address' => $_POST['address'] ?? null,
        'school' => $_POST['school'],
        'grad_year' => $_POST['grad_year'],
        'avg_score' => $_POST['avg_score'],
        'subject_scores' => $subject_scores,
        'certificates' => $certificates,
        'interests' => $interests,
        'values' => $values,
        'interest_desc' => $_POST['interest_desc'] ?? null,
        'skills' => $skills,
        'skill_levels' => $skill_levels,
        'experience' => $_POST['experience'] ?? null,
        'goals' => $_POST['goals'],
        'initial_interests' => $initial_interests,
        'source' => $_POST['source'] ?? null
    ]);

    // Build prompt cho OpenAI từ data
    $prompt = "Dựa trên dữ liệu: tên $name, tuổi $age, sở thích $interests, kỹ năng $skills, mục tiêu " . $_POST['goals'] . ", điểm trung bình " . $_POST['avg_score'] . ". Gợi ý 3 ngành học phù hợp tại FPT Polytechnic (CNTT, Kinh doanh, Thiết kế) với lý do chi tiết và tỷ lệ phù hợp (ví dụ: CNTT 70%).";

    // Gọi API Cursor (nếu cấu hình), nếu không fallback OpenAI, cuối cùng fallback thông báo.
    $ai_result = 'AI chưa được cấu hình.';
    if (CURSOR_API_URL && CURSOR_API_KEY) {
        $ch = curl_init(rtrim(CURSOR_API_URL, '/') . '/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . CURSOR_API_KEY
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => CURSOR_MODEL,
            'messages' => [
                ['role' => 'system', 'content' => 'Bạn là cố vấn hướng nghiệp cho học sinh Việt Nam. Trả lời súc tích, thực tế.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.4
        ]));
        $response = curl_exec($ch);
        if ($response !== false) {
            $ai_data = json_decode($response, true);
            $ai_result = $ai_data['choices'][0]['message']['content'] ?? $ai_result;
        }
        curl_close($ch);
    } elseif (API_KEY_OPENAI) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . API_KEY_OPENAI
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Bạn là cố vấn hướng nghiệp cho học sinh Việt Nam. Trả lời súc tích, thực tế.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.4
        ]));
        $response = curl_exec($ch);
        if ($response !== false) {
            $ai_data = json_decode($response, true);
            $ai_result = $ai_data['choices'][0]['message']['content'] ?? $ai_result;
        }
        curl_close($ch);
    }

    // Update ai_result vào DB
    $update_stmt = $pdo->prepare("UPDATE customers SET ai_result = :ai_result WHERE id = :id");
    $update_stmt->execute(['ai_result' => $ai_result, 'id' => $pdo->lastInsertId()]);

    // Trả JSON cho frontend
    echo json_encode(['success' => true, 'result' => $ai_result]);
    exit;
}
?>