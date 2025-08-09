<?php
require 'config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hướng nghiệp AI - Trang chủ</title>
  <link rel="stylesheet" href="assets/style.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    .hero { background:#0b7a75; color:#fff; padding:80px 20px; }
    .hero .inner { max-width:960px; margin:0 auto; }
    .hero h1 { margin:0 0 12px; font-size:34px; }
    .hero p { margin:0 0 24px; opacity:.95; }
    .cta-buttons a { display:inline-block; background:#fff; color:#0b7a75; padding:12px 18px; margin-right:12px; border-radius:6px; text-decoration:none; font-weight:600; }
    .section { max-width:960px; margin:40px auto; padding:0 20px; }
    .cards { display:grid; gap:16px; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); }
    .card { background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:18px; }
    .card h3 { margin-top:0; }
  </style>
</head>
<body>
  <div class="hero">
    <div class="inner">
      <h1>Vì bạn không cô đơn trên hành trình phát triển nghề nghiệp</h1>
      <p>AI đồng hành cùng bạn khám phá thế mạnh, định hướng ngành nghề, lộ trình học và kỹ năng cần rèn luyện.</p>
      <div class="cta-buttons">
        <a href="chat.php">Tôi muốn được tư vấn</a>
        <a href="index.php">Trải nghiệm gợi ý nhanh</a>
      </div>
    </div>
  </div>

  <div class="section">
    <h2>Lợi ích bạn nhận được</h2>
    <div class="cards">
      <div class="card">
        <h3>Tư vấn cá nhân hoá</h3>
        <p>Gợi ý ngành/nghề phù hợp dựa trên sở thích, điểm mạnh, và mục tiêu cá nhân.</p>
      </div>
      <div class="card">
        <h3>Lộ trình rõ ràng</h3>
        <p>Đề xuất môn nên học, kỹ năng cần cải thiện và kế hoạch 3-6-12 tháng.</p>
      </div>
      <div class="card">
        <h3>Tài nguyên học tập</h3>
        <p>Tập hợp nguồn tài liệu chất lượng giúp bạn tự chủ trên hành trình nghề nghiệp.</p>
      </div>
    </div>
  </div>

  <div class="section" style="text-align:center; margin-bottom:60px;">
    <a class="button" href="chat.php" style="display:inline-block;background:#0b7a75;color:#fff;padding:12px 18px;border-radius:6px;text-decoration:none;">Bắt đầu trò chuyện với AI</a>
  </div>
</body>
</html>


