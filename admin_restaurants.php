<?php
session_start();
require_once __DIR__ . '/db.php';
$pdo = getDBConnection();

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

// Bulk approve
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bulk_ids'])) {
    $ids = array_map('intval', $_POST['bulk_ids']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $pdo->prepare("UPDATE users SET is_approved=1 WHERE id IN ($in)")->execute($ids);
}

$sql = "SELECT id, name, email, is_approved, created_at FROM users WHERE 1";
$params = [];
if ($q !== '') {
    $sql .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$q%"; $params[] = "%$q%";
}
if ($status === 'approved') { $sql .= " AND is_approved=1"; }
if ($status === 'pending') { $sql .= " AND is_approved=0"; }
$sql .= " ORDER BY created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin • Restaurants</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php if (!(isset($_GET['embed']) && $_GET['embed'] == '1')) include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-4">Restaurants</h1>
    <form class="flex gap-3 items-end mb-4" method="GET">
      <div>
        <label class="block text-sm">Search</label>
        <input class="border border-slate-200 rounded-lg px-3 py-2" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Name or email">
      </div>
      <div>
        <label class="block text-sm">Status</label>
        <select class="border border-slate-200 rounded-lg px-3 py-2" name="status">
          <option value="">All</option>
          <option value="approved" <?php echo $status==='approved'?'selected':''; ?>>Approved</option>
          <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
        </select>
      </div>
      <button class="fa-btn" type="submit">Filter</button>
    </form>

    <form method="POST" class="fa-card p-4">
      <div class="flex items-center gap-2 mb-3">
        <button class="fa-btn" type="submit">Approve Selected</button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="py-2"><input type="checkbox" id="checkAll"></th>
              <th>Name</th><th>Email</th><th>Created</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr class="border-t">
              <td class="py-2"><input type="checkbox" name="bulk_ids[]" value="<?php echo (int)$r['id']; ?>"></td>
              <td><?php echo htmlspecialchars($r['name'] ?: '—'); ?></td>
              <td><?php echo htmlspecialchars($r['email']); ?></td>
              <td><?php echo htmlspecialchars($r['created_at']); ?></td>
              <td><?php echo ((int)$r['is_approved']===1)?'Approved':'Pending'; ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
  <script>
    const all = document.getElementById('checkAll');
    if (all) all.addEventListener('change', e => {
      document.querySelectorAll('input[name="bulk_ids[]"]').forEach(cb => cb.checked = e.target.checked);
    });
  </script>
</body>
</html>


