<?php
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();
ensureSchema($pdo);

$isEmbed = isset($_GET['embed']);
$pending = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='pending'")->fetchColumn();
$active = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();

// Try to fetch with user_id; if the legacy table lacks the column, attempt to add it, then fallback without it
$rows = [];
$hasUserId = true;
try {
  $recent = $pdo->query("SELECT id, user_id, plan, status, created_at FROM subscriptions ORDER BY created_at DESC LIMIT 20");
  if ($recent) { $rows = $recent->fetchAll(PDO::FETCH_ASSOC); }
} catch (Throwable $e) {
  $hasUserId = false;
  try { $pdo->exec("ALTER TABLE subscriptions ADD COLUMN IF NOT EXISTS user_id INT NULL AFTER id"); $hasUserId = true; } catch (Throwable $ignored) {}
  try {
    if ($hasUserId) {
      $recent = $pdo->query("SELECT id, user_id, plan, status, created_at FROM subscriptions ORDER BY created_at DESC LIMIT 20");
      if ($recent) { $rows = $recent->fetchAll(PDO::FETCH_ASSOC); }
    } else {
      $recent = $pdo->query("SELECT id, plan, status, created_at FROM subscriptions ORDER BY created_at DESC LIMIT 20");
      if ($recent) { $rows = $recent->fetchAll(PDO::FETCH_ASSOC); }
    }
  } catch (Throwable $e2) {
    try {
      $recent = $pdo->query("SELECT id, plan, status, created_at FROM subscriptions ORDER BY created_at DESC LIMIT 20");
      if ($recent) { $rows = $recent->fetchAll(PDO::FETCH_ASSOC); }
    } catch (Throwable $e3) { $rows = []; }
  }
}
?>
<?php if (!$isEmbed): ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payments & Subscriptions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <div class="max-w-5xl mx-auto px-6 py-8">
<?php endif; ?>

  <div class="grid md:grid-cols-2 gap-6 mb-6">
    <div class="fa-card p-6">
      <div class="text-sm text-slate-500">Pending</div>
      <div class="text-3xl font-extrabold"><?php echo $pending; ?></div>
    </div>
    <div class="fa-card p-6">
      <div class="text-sm text-slate-500">Active</div>
      <div class="text-3xl font-extrabold"><?php echo $active; ?></div>
    </div>
  </div>

  <div class="fa-card p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Recent Subscriptions</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-slate-500">
            <th class="py-2 pr-4">ID</th>
            <th class="py-2 pr-4">User</th>
            <th class="py-2 pr-4">Plan</th>
            <th class="py-2 pr-4">Status</th>
            <th class="py-2 pr-4">Created</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($rows) === 0): ?>
            <tr>
              <td colspan="5" class="py-4 text-slate-500">No subscriptions yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <tr class="border-t border-slate-100">
                <td class="py-2 pr-4"><?php echo htmlspecialchars($r['id']); ?></td>
                <td class="py-2 pr-4"><?php echo isset($r['user_id']) && $r['user_id'] !== null ? ('#' . htmlspecialchars($r['user_id'])) : '-'; ?></td>
                <td class="py-2 pr-4"><?php echo htmlspecialchars($r['plan']); ?></td>
                <td class="py-2 pr-4">
                  <span class="px-2 py-1 rounded text-xs <?php echo $r['status']==='active' ? 'bg-green-100 text-green-700' : ($r['status']==='pending' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-700'); ?>">
                    <?php echo htmlspecialchars($r['status']); ?>
                  </span>
                </td>
                <td class="py-2 pr-4"><?php echo htmlspecialchars($r['created_at']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php if (!$isEmbed): ?>
  </div>
</body>
</html>
<?php endif; ?>


