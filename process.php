<?php
require_once 'db.php';
require_once 'env.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

try {
    // Basic validation for required fields
    $requiredFields = ['name','gender','age','email','school','grad_year','avg_score','goals'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            throw new Exception("Thiếu trường bắt buộc: $field");
        }
    }

    $pdo->beginTransaction();

    // Chuyển đổi dữ liệu từ form
    $interests_json = json_encode($_POST['interests'] ?? []);
    $skills_json = json_encode(array_keys($_POST['skills'] ?? []));
    $skill_levels_json = json_encode($_POST['skills'] ?? []);
    $initial_interests_json = $interests_json;

    // 1. Lưu thông tin khách hàng
    $stmt = $pdo->prepare("
        INSERT INTO customers 
        (name, gender, age, email, phone, address, school, grad_year, avg_score, 
         interests, interest_desc, skills, skill_levels, experience, goals, initial_interests, source) 
        VALUES 
        (:name, :gender, :age, :email, :phone, :address, :school, :grad_year, :avg_score, 
         :interests, :interest_desc, :skills, :skill_levels, :experience, :goals, :initial_interests, :source)
    ");
    $stmt->execute([
        ':name' => $_POST['name'],
        ':gender' => $_POST['gender'],
        ':age' => $_POST['age'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'] ?? null,
        ':address' => $_POST['address'] ?? null,
        ':school' => $_POST['school'],
        ':grad_year' => $_POST['grad_year'],
        ':avg_score' => $_POST['avg_score'],
        ':interests' => $interests_json,
        ':interest_desc' => $_POST['interest_desc'] ?? null,
        ':skills' => $skills_json,
        ':skill_levels' => $skill_levels_json,
        ':experience' => $_POST['experience'] ?? null,
        ':goals' => $_POST['goals'],
        ':initial_interests' => $initial_interests_json,
        ':source' => 'web_form'
    ]);

    $customer_id = $pdo->lastInsertId();

    // 2. Lưu ngành quan tâm
    if (!empty($_POST['interests'])) {
        $stmt = $pdo->prepare("INSERT INTO customer_interests (customer_id, major_id) VALUES (:cid, :mid)");
        foreach ($_POST['interests'] as $major_id) {
            $stmt->execute([':cid' => $customer_id, ':mid' => $major_id]);
        }
    }

    // 3. Lưu kỹ năng
    if (!empty($_POST['skills'])) {
        $stmt = $pdo->prepare("INSERT INTO customer_skills (customer_id, skill_id, level) VALUES (:cid, :sid, :lvl)");
        foreach ($_POST['skills'] as $skill_id => $level) {
            if (!empty($level)) {
                $stmt->execute([':cid' => $customer_id, ':sid' => $skill_id, ':lvl' => $level]);
            }
        }
    }

    // 4. Tạo prompt cho AI
    $prompt = "Bạn là chuyên gia tư vấn hướng nghiệp. 
    Dưới đây là thông tin của một khách hàng:\n\n" . json_encode($_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) .
    "\n\nDựa trên sở thích, kỹ năng và mục tiêu nghề nghiệp, hãy gợi ý ngành học phù hợp nhất và giải thích lý do.";

    // 5. Gọi OpenAI (nếu có API key), nếu không tạo gợi ý fallback
    $apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : getenv('OPENAI_API_KEY');
    $ai_result = null;
    if (!empty($apiKey)) {
        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Bearer " . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => "Bạn là chuyên gia hướng nghiệp, trả lời bằng tiếng Việt."],
                ["role" => "user", "content" => $prompt]
            ],
            "max_tokens" => 500
        ]));
        $response = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErrNo !== 0) {
            $ai_result = 'Không thể kết nối AI: ' . $curlErr;
        } else {
            $ai_data = json_decode($response, true);
            $ai_result = $ai_data['choices'][0]['message']['content'] ?? (
                ($httpCode >= 400) ? ('Lỗi AI HTTP ' . $httpCode) : 'Không nhận được phản hồi từ AI'
            );
        }
    } else {
        $ai_result = 'Chưa cấu hình OPENAI_API_KEY. Vui lòng liên hệ tư vấn viên để được hỗ trợ trực tiếp.';
    }

    // 6. Cập nhật kết quả AI
    $stmt = $pdo->prepare("UPDATE customers SET ai_result = :ai WHERE id = :cid");
    $stmt->execute([':ai' => $ai_result, ':cid' => $customer_id]);

    // 7. Lưu request
    $stmt = $pdo->prepare("
        INSERT INTO requests (customer_id, raw_input, prompt, ai_response) 
        VALUES (:cid, :raw, :prompt, :ai)
    ");
    $stmt->execute([
        ':cid' => $customer_id,
        ':raw' => json_encode($_POST, JSON_UNESCAPED_UNICODE),
        ':prompt' => $prompt,
        ':ai' => $ai_result
    ]);

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Tư vấn thành công",
        "ai_result" => $ai_result
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
