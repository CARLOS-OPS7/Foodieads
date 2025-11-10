<?php
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();

// Minimal mock data for now; replace with real ads table if available
$rows = [];
try {
  $ads = $pdo->query("SELECT id, title, status FROM ads ORDER BY id DESC LIMIT 20");
  if ($ads) { $rows = $ads->fetchAll(PDO::FETCH_ASSOC); }
} catch (Throwable $e) {
  $rows = [];
}
$isEmbed = isset($_GET['embed']);
?>
<?php if (!$isEmbed): ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Ads</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <div class="max-w-5xl mx-auto px-6 py-8">
<?php endif; ?>

  <div class="fa-card p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Manage Ads</h2>
      <a href="#" class="px-3 py-2 rounded bg-indigo-600 text-white text-sm">New Ad</a>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="text-left text-slate-500">
            <th class="py-2 pr-4">ID</th>
            <th class="py-2 pr-4">Title</th>
            <th class="py-2 pr-4">Status</th>
            <th class="py-2 pr-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($rows) === 0): ?>
            <tr>
              <td colspan="4" class="py-4 text-slate-500">No ads found.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $ad): ?>
              <tr class="border-t border-slate-100">
                <td class="py-2 pr-4"><?php echo htmlspecialchars($ad['id']); ?></td>
                <td class="py-2 pr-4"><?php echo htmlspecialchars($ad['title']); ?></td>
                <td class="py-2 pr-4">
                  <span class="px-2 py-1 rounded text-xs <?php echo $ad['status']==='active' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-700'; ?>">
                    <?php echo htmlspecialchars($ad['status'] ?: 'draft'); ?>
                  </span>
                </td>
                <td class="py-2 pr-4 space-x-2">
                  <a href="#" class="text-indigo-600">Edit</a>
                  <a href="#" class="text-rose-600">Delete</a>
                </td>
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


