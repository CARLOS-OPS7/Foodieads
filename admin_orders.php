<?php
session_start();
require_once __DIR__ . '/db.php';
$pdo = getDBConnection();

// Demo orders table if not present
try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant VARCHAR(120) NOT NULL,
    amount INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$sql = "SELECT * FROM orders WHERE 1"; $p=[];
if ($q !== '') { $sql .= " AND (restaurant LIKE ?)"; $p[] = "%$q%"; }
if ($status !== '') { $sql .= " AND status=?"; $p[] = $status; }
if ($from !== '') { $sql .= " AND created_at >= ?"; $p[] = $from; }
if ($to !== '') { $sql .= " AND created_at <= ?"; $p[] = $to; }
$sql .= " ORDER BY created_at DESC LIMIT 300";
$st = $pdo->prepare($sql); $st->execute($p); $rows = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin • Orders</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php if (!(isset($_GET['embed']) && $_GET['embed'] == '1')) include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-4">Orders</h1>
    <form method="GET" class="flex flex-wrap gap-3 items-end mb-4">
      <div>
        <label class="block text-sm">Restaurant</label>
        <input class="border border-slate-200 rounded-lg px-3 py-2" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Search...">
      </div>
      <div>
        <label class="block text-sm">Status</label>
        <select class="border border-slate-200 rounded-lg px-3 py-2" name="status">
          <option value="">All</option>
          <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
          <option value="paid" <?php echo $status==='paid'?'selected':''; ?>>Paid</option>
          <option value="cancelled" <?php echo $status==='cancelled'?'selected':''; ?>>Cancelled</option>
        </select>
      </div>
      <div>
        <label class="block text-sm">From</label>
        <input type="date" class="border border-slate-200 rounded-lg px-3 py-2" name="from" value="<?php echo htmlspecialchars($from); ?>">
      </div>
      <div>
        <label class="block text-sm">To</label>
        <input type="date" class="border border-slate-200 rounded-lg px-3 py-2" name="to" value="<?php echo htmlspecialchars($to); ?>">
      </div>
      <button class="fa-btn" type="submit">Filter</button>
    </form>

    <div class="fa-card p-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-slate-500">
            <th class="py-2">ID</th><th>Restaurant</th><th>Amount</th><th>Status</th><th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr class="border-t">
            <td class="py-2"><?php echo (int)$r['id']; ?></td>
            <td><?php echo htmlspecialchars($r['restaurant']); ?></td>
            <td>KES <?php echo (int)$r['amount']; ?></td>
            <td class="capitalize"><?php echo htmlspecialchars($r['status']); ?></td>
            <td><?php echo htmlspecialchars($r['created_at']); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>


