<?php
require 'config.php';
$id = (int)($_GET['id'] ?? 0);
$pdo = pdo();
$stmt = $pdo->prepare("SELECT r.*, u.name, u.email FROM recommendations r LEFT JOIN users u ON u.id = r.user_id WHERE r.id = :id");
$stmt->execute(['id'=>$id]);
$row = $stmt->fetch();
if (!$row) {
    echo "Không tìm thấy kết quả."; exit;
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Kết quả gợi ý</title></head>
<body>
  <div class="container">
    <h1>Kết quả gợi ý</h1>
    <p><strong>Tên:</strong> <?=htmlspecialchars($row['name'])?> <strong>Email:</strong> <?=htmlspecialchars($row['email'])?></p>
    <h2>Gợi ý</h2>
    <pre style="white-space:pre-wrap;"><?=htmlspecialchars($row['result_text'])?></pre>
    <h3>Ngành được gợi ý (mã):</h3>
    <p><?=htmlspecialchars($row['suggested_major_codes'])?></p>
    <p><a href="index.php">Quay lại</a></p>
  </div>
</body></html>
