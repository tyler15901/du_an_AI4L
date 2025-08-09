<?php
require 'config.php';
$pdo = pdo();
$q = $pdo->query("SELECT r.id, u.name, r.suggested_major_codes, r.created_at FROM recommendations r LEFT JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC");
$rows = $q->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>Admin - Recommendations</title></head>
<body>
  <h1>Danh sách kết quả</h1>
  <table border="1" cellpadding="8">
    <tr><th>ID</th><th>Name</th><th>Codes</th><th>Thời gian</th><th>Chi tiết</th></tr>
    <?php foreach($rows as $r): ?>
    <tr>
      <td><?=$r['id']?></td>
      <td><?=htmlspecialchars($r['name'])?></td>
      <td><?=htmlspecialchars($r['suggested_major_codes'])?></td>
      <td><?=$r['created_at']?></td>
      <td><a href="result.php?id=<?=$r['id']?>">Xem</a></td>
    </tr>
    <?php endforeach;?>
  </table>
</body></html>
