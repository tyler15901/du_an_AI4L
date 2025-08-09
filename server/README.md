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
- `GET /api/majors` — danh sách ngành (từ DB)

### Ghi chú
- Không commit API key.
- Thay model nếu tài khoản không có `gpt-4o-mini`.

### JSON Server (tuỳ chọn, cho demo dữ liệu ngành/chương trình)
1. Cài JSON Server (nếu chưa):
   - Toàn cục: `npm install -g json-server`
   - Hoặc cục bộ (đã thêm vào devDependencies): `npm install`
2. Chạy với file `data/majors_curriculum.json` (ở thư mục gốc dự án):
   - `npx json-server --watch data/majors_curriculum.json --port 3000`
   - Hoặc dùng script: `npm run server`
3. Truy cập: `http://localhost:3000/majors`
4. Tuỳ chọn nâng cao:
   - Dùng `_embed` để lấy kèm liên kết
   - Dùng `_expand` cho quan hệ 1-1 (nếu định nghĩa)


