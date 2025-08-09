## AI Major Advisor API (Node + Express + MySQL)

### Yêu cầu
- Node 18+
- MySQL (Laragon có sẵn)

### Cấu hình
Tạo file `.env` trong thư mục `server/`:

```
OPENAI_API_KEY=sk-xxx
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=duan_ai
PORT=3000
```

### Khởi tạo database
Chạy các script SQL:

```
mysql -u root -p < sql/schema.sql
mysql -u root -p < sql/seed.sql
```

### Cài đặt & chạy
```
npm i
npm run dev
```

### Endpoints
- `GET /health` — kiểm tra kết nối DB
- `POST /api/recommend` — body: `{ profile: {...} }` → lưu profile, tính điểm, gọi AI, trả về đề xuất
- `GET /api/recommendations/:profileId` — xem lịch sử kết quả của hồ sơ

### Ghi chú
- Không commit API key.
- Thay model nếu tài khoản không có `gpt-4o-mini`.


