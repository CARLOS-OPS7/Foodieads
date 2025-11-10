<?php
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();
ensureSchema($pdo);

header('Content-Type: text/html; charset=utf-8');

// Handle actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action) {
  try {
    if ($action === 'create') {
      $title = trim($_POST['title'] ?? '');
      $message = trim($_POST['message'] ?? '');
      $image = trim($_POST['image_url'] ?? '');
      $stmt = $pdo->prepare("INSERT INTO campaigns (title, message, image_url, is_active) VALUES (?,?,?,1)");
      $stmt->execute([$title, $message ?: null, $image ?: null]);
    } elseif ($action === 'toggle') {
      $id = (int)($_POST['id'] ?? 0);
      $pdo->prepare("UPDATE campaigns SET is_active = 1 - is_active WHERE id=?")->execute([$id]);
    } elseif ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      $pdo->prepare("DELETE FROM campaigns WHERE id=?")->execute([$id]);
    } elseif ($action === 'update') {
      $id = (int)($_POST['id'] ?? 0);
      $title = trim($_POST['title'] ?? '');
      $message = trim($_POST['message'] ?? '');
      $image = trim($_POST['image_url'] ?? '');
      $stmt = $pdo->prepare("UPDATE campaigns SET title=?, message=?, image_url=? WHERE id=?");
      $stmt->execute([$title, $message ?: null, $image ?: null, $id]);
    }
  } catch (Throwable $e) {
    // swallow for UI simplicity
  }
}

// List campaigns
$rows = [];
try {
  $rows = $pdo->query("SELECT id, title, message, image_url, is_active, created_at FROM campaigns ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Throwable $e) { $rows = []; }

$isEmbed = isset($_GET['embed']);
?>
<?php if (!$isEmbed): ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Campaigns</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
</head>
<body class="bg-slate-50">
  <div class="max-w-5xl mx-auto px-6 py-8">
<?php endif; ?>

  <div class="fa-card p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold">Campaigns Library</h2>
    </div>

    <form hx-post="admin_campaigns.php?embed=1" hx-target="#camp-list" hx-swap="outerHTML" class="grid md:grid-cols-4 gap-3 mb-6">
      <input class="border border-slate-200 rounded px-3 py-2" name="title" placeholder="Title" required>
      <input class="border border-slate-200 rounded px-3 py-2" name="message" placeholder="Message (optional)">
      <input class="border border-slate-200 rounded px-3 py-2" name="image_url" placeholder="Image URL (optional)">
      <input type="hidden" name="action" value="create">
      <button class="fa-btn">Add Campaign</button>
    </form>

    <div id="camp-list">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="text-left text-slate-500">
            <tr>
              <th class="py-2 pr-2">ID</th>
              <th class="py-2 pr-2">Preview</th>
              <th class="py-2 pr-2">Title</th>
              <th class="py-2 pr-2">Message</th>
              <th class="py-2 pr-2">Active</th>
              <th class="py-2 pr-2">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
            <tr class="border-t border-slate-200 align-top">
              <td class="py-2 pr-2 font-mono text-xs">#<?php echo (int)$r['id']; ?></td>
              <td class="py-2 pr-2">
                <?php if (!empty($r['image_url'])): ?>
                  <img src="<?php echo htmlspecialchars($r['image_url']); ?>" alt="" class="h-12 w-12 object-cover rounded">
                <?php else: ?>
                  <div class="h-12 w-12 rounded bg-slate-100 grid place-items-center">—</div>
                <?php endif; ?>
              </td>
              <td class="py-2 pr-2">
                <form hx-post="admin_campaigns.php?embed=1" hx-target="#camp-list" hx-swap="outerHTML" class="space-y-1">
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <input type="hidden" name="action" value="update">
                  <input class="border border-slate-200 rounded px-2 py-1 w-full" name="title" value="<?php echo htmlspecialchars($r['title']); ?>">
                  <input class="border border-slate-200 rounded px-2 py-1 w-full" name="message" value="<?php echo htmlspecialchars($r['message'] ?? ''); ?>" placeholder="Message">
                  <input class="border border-slate-200 rounded px-2 py-1 w-full" name="image_url" value="<?php echo htmlspecialchars($r['image_url'] ?? ''); ?>" placeholder="Image URL">
                  <button class="px-2 py-1 rounded bg-slate-900 text-white text-xs">Save</button>
                </form>
              </td>
              <td class="py-2 pr-2 text-slate-600 max-w-xs">&nbsp;</td>
              <td class="py-2 pr-2">
                <form hx-post="admin_campaigns.php?embed=1" hx-target="#camp-list" hx-swap="outerHTML">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <button class="px-2 py-1 rounded text-xs <?php echo ((int)$r['is_active']===1?'bg-green-600 text-white':'bg-slate-200'); ?>"><?php echo ((int)$r['is_active']===1?'Active':'Inactive'); ?></button>
                </form>
              </td>
              <td class="py-2 pr-2">
                <form hx-post="admin_campaigns.php?embed=1" hx-target="#camp-list" hx-swap="outerHTML" onsubmit="return confirm('Delete this campaign?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                  <button class="px-2 py-1 rounded bg-rose-600 text-white text-xs">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="py-6 text-center text-slate-500">No campaigns yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

<?php if (!$isEmbed): ?>
  </div>
</body>
</html>
<?php endif; ?>


