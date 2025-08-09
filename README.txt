Hướng dẫn nhanh:
1. Copy thư mục fpt_recommender vào htdocs (XAMPP) hoặc www (MAMP).
2. Import file db.sql trong phpMyAdmin để tạo database và bảng.
3. Chỉnh config.php nếu cần (DB_USER, DB_PASS).
4. Mở http://localhost/duanAI4L/landing.php để vào trang chủ mới.
   - Chat AI tại http://localhost/duanAI4L/chat.php
   - Gợi ý nhanh cũ tại http://localhost/duanAI4L/index.php
5. Mặc định project dùng local AI (USE_LOCAL_API = true). Để dùng OpenAI, set USE_LOCAL_API = false và nhập OPENAI_API_KEY trong config.php.
6. Bảng mới: conversations, messages để lưu lịch sử chat.
