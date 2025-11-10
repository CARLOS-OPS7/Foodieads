<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';

// TODO: Add admin auth check
$pdo = getPdo();
ensureSchema($pdo);

// Handle single approve/reject and bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_id'])) {
        activateSubscription($pdo, (int)$_POST['approve_id']);
    }
    if (isset($_POST['reject_id'])) {
        failSubscription($pdo, (int)$_POST['reject_id']);
    }
    if (!empty($_POST['bulk_ids'])) {
        foreach ($_POST['bulk_ids'] as $sid) {
            if ($_POST['bulk_action'] === 'approve') activateSubscription($pdo, (int)$sid);
            if ($_POST['bulk_action'] === 'reject') failSubscription($pdo, (int)$sid);
        }
    }
}

$pending = listPendingSubscriptions($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin • Subscriptions</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php if (!(isset($_GET['embed']) && $_GET['embed'] == '1')) include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-4">Pending Subscriptions</h1>
    <form method="POST" class="fa-card p-4 mb-4">
      <div class="flex items-center gap-2">
        <select name="bulk_action" class="border border-slate-200 rounded-lg px-3 py-2">
          <option value="approve">Approve</option>
          <option value="reject">Reject</option>
        </select>
        <button class="fa-btn" type="submit">Apply to Selected</button>
      </div>
      <div class="overflow-x-auto mt-4">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-slate-500">
              <th class="py-2"><input type="checkbox" id="checkAll"></th>
              <th>User</th><th>Plan</th><th>Amount</th><th>MPesa Code</th><th>Created</th><th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pending as $p): ?>
            <tr class="border-t">
              <td class="py-2"><input type="checkbox" name="bulk_ids[]" value="<?php echo (int)$p['id']; ?>"></td>
              <td><?php echo htmlspecialchars($p['name']); ?> <span class="text-slate-500 text-xs"><?php echo htmlspecialchars($p['email']); ?></span></td>
              <td class="capitalize"><?php echo htmlspecialchars($p['plan']); ?></td>
              <td>KES <?php echo (int)$p['amount']; ?></td>
              <td><?php echo htmlspecialchars($p['mpesa_code']); ?></td>
              <td><?php echo htmlspecialchars($p['created_at']); ?></td>
              <td>
                <button name="approve_id" value="<?php echo (int)$p['id']; ?>" class="px-3 py-1 rounded-md bg-green-600 text-white">Approve</button>
                <button name="reject_id" value="<?php echo (int)$p['id']; ?>" class="px-3 py-1 rounded-md border border-slate-300 ml-2">Reject</button>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($pending)): ?>
            <tr><td class="py-3 text-slate-500" colspan="7">No pending subscriptions.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
  <script>
    const all = document.getElementById('checkAll');
    if (all) {
      all.addEventListener('change', (e) => {
        document.querySelectorAll('input[name="bulk_ids[]"]').forEach(cb => cb.checked = e.target.checked);
      });
    }
  </script>
</body>
</html>


