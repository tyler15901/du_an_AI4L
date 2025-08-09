<?php
require 'config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Gợi ý ngành học - FPT</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="container">
    <h1>Ứng dụng gợi ý ngành học</h1>
    <form action="analyze.php" method="post">
      <label>Tên:</label><br>
      <input type="text" name="name" required><br><br>

      <label>Email:</label><br>
      <input type="email" name="email"><br><br>

      <label>Sở thích / kỹ năng / môn học yêu thích (viết ngắn):</label><br>
      <textarea name="preferences" rows="4" required></textarea><br><br>

      <label>Điểm các môn / năng lực (ví dụ: Toán 8, Lý 7, Học lực: TB):</label><br>
      <input type="text" name="scores"><br><br>

      <label>Thói quen / tính cách (ví dụ: hướng ngoại, thích sáng tạo,...):</label><br>
      <input type="text" name="personality"><br><br>

      <button type="submit">Gợi ý ngành</button>
    </form>
  </div>
</body>
</html>
