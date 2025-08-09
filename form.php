<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tư vấn ngành học</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-4">
    <h2 class="mb-4">Form Tư vấn Ngành học</h2>
    <form id="adviceForm" action="process.php" method="POST">
        <!-- Thông tin cá nhân -->
        <div class="card mb-4">
            <div class="card-header">Thông tin cá nhân</div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Họ và tên</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Giới tính</label>
                    <select name="gender" class="form-control" required>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Tuổi</label>
                    <input type="number" name="age" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Trường học</label>
                    <input type="text" name="school" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Năm tốt nghiệp</label>
                    <input type="number" name="grad_year" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Điểm trung bình</label>
                    <input type="number" step="0.01" name="avg_score" class="form-control" required>
                </div>
            </div>
        </div>

        <!-- Sở thích ngành -->
        <div class="card mb-4">
            <div class="card-header">Ngành học quan tâm</div>
            <div class="card-body">
                <p>Chọn các ngành bạn quan tâm:</p>
                <div id="majors-list">
                    <!-- Sẽ load từ DB qua PHP -->
                    <?php
                    require_once 'db.php';
                    $stmt = $pdo->query("SELECT id, name FROM majors ORDER BY parent_id, name");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $name = htmlspecialchars($row['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        echo '<div class="form-check">
                                <input class="form-check-input" type="checkbox" name="interests[]" value="'.$row['id'].'">
                                <label class="form-check-label">'.$name.'</label>
                              </div>';
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Kỹ năng -->
        <div class="card mb-4">
            <div class="card-header">Kỹ năng</div>
            <div class="card-body">
                <p>Chọn kỹ năng bạn có:</p>
                <?php
                $stmt = $pdo->query("SELECT id, skill FROM skills ORDER BY skill");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $skill = htmlspecialchars($row['skill'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    echo '<div class="mb-2">
                            <label>'.$skill.'</label>
                            <select name="skills['.$row['id'].']" class="form-control">
                                <option value="">Chưa có</option>
                                <option value="Cơ bản">Cơ bản</option>
                                <option value="Khá">Khá</option>
                                <option value="Tốt">Tốt</option>
                            </select>
                          </div>';
                }
                ?>
            </div>
        </div>

        <!-- Mô tả thêm -->
        <div class="card mb-4">
            <div class="card-header">Thông tin bổ sung</div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Mô tả sở thích</label>
                    <textarea name="interest_desc" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Kinh nghiệm</label>
                    <textarea name="experience" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <label>Mục tiêu nghề nghiệp</label>
                    <textarea name="goals" class="form-control" required></textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Gửi tư vấn</button>
    </form>

<div id="result" class="mt-4" style="display:none;">
    <h4>Kết quả tư vấn</h4>
    <div id="aiOutput" style="white-space:pre-line;"></div>
</div>

<script>
const formEl = document.getElementById('adviceForm');
if (formEl) {
  formEl.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(formEl);
    fetch('process.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        document.getElementById('result').style.display = 'block';
        document.getElementById('aiOutput').textContent = data.ai_result;
      } else {
        alert('Lỗi: ' + data.message);
      }
    })
    .catch(err => alert('Lỗi kết nối: ' + err));
  });
}
</script>

</div>
</body>
</html>
