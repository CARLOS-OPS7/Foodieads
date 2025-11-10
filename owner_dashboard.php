<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$pdo = getPdo();
ensureSchema($pdo);

// Fetch latest subscription and history
$active = getActiveSubscription($pdo, $userId);
$history = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$history->execute([$userId]);
$rows = $history->fetchAll(PDO::FETCH_ASSOC);

// Registration status
$userStmt = $pdo->prepare("SELECT is_approved, created_at, name FROM users WHERE id=?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$name = $user['name'] ?? 'Owner';

function stepClass($done) { return $done ? 'bg-green-600 text-white' : 'bg-slate-200 text-slate-600'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Owner Dashboard - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script>
    <?php include __DIR__ . '/supabase_config.php'; echo supabase_js_config(); ?>
  </script>
  <script src="https://unpkg.com/@supabase/supabase-js@2"></script>
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Welcome, <?php echo htmlspecialchars($name); ?></h1>
      <a href="upgrade.php" class="fa-btn">Upgrade Plan</a>
    </div>

    <!-- Progress tracker -->
    <?php
      $hasAny = count($rows) > 0;
      $isPaidActive = (bool)$active;
      $approved = (int)($user['is_approved'] ?? 0) === 1;
    ?>
    <div class="fa-card p-6 mb-8">
      <h2 class="font-semibold mb-3">Progress</h2>
      <div class="grid grid-cols-4 gap-3">
        <div class="rounded-lg px-3 py-2 text-center <?php echo stepClass(true); ?>">Registration</div>
        <div class="rounded-lg px-3 py-2 text-center <?php echo stepClass($hasAny); ?>">Payment</div>
        <div class="rounded-lg px-3 py-2 text-center <?php echo stepClass($approved); ?>">Approval</div>
        <div class="rounded-lg px-3 py-2 text-center <?php echo stepClass($isPaidActive); ?>">Active</div>
      </div>
    </div>

    <!-- Subscription status -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="fa-card p-6">
        <h3 class="font-semibold mb-2">Current Subscription</h3>
        <?php if ($active): ?>
          <p class="text-slate-700">Plan: <strong><?php echo htmlspecialchars(ucfirst($active['plan'])); ?></strong></p>
          <p class="text-slate-700">Ends: <strong><?php echo htmlspecialchars($active['end_date']); ?></strong></p>
          <p class="text-green-600 font-semibold mt-2">Subscription Active ✅</p>
        <?php else: ?>
          <p class="text-slate-600">No active subscription.</p>
          <a href="upgrade.php" class="fa-btn mt-3 inline-block">Upgrade Now</a>
        <?php endif; ?>
      </div>
      <div class="fa-card p-6">
        <h3 class="font-semibold mb-2">Payment History</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-slate-500">
                <th class="py-2">Date</th><th>Plan</th><th>Amount</th><th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $r): ?>
              <tr class="border-t">
                <td class="py-2"><?php echo htmlspecialchars($r['created_at']); ?></td>
                <td><?php echo htmlspecialchars($r['plan']); ?></td>
                <td>KES <?php echo (int)$r['amount']; ?></td>
                <td class="capitalize"><?php echo htmlspecialchars($r['status']); ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($rows)): ?>
              <tr><td class="py-2 text-slate-500" colspan="4">No payments yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
<script>
  // Realtime: listen for subscription status updates for this user
  (function(){
    if (!window.__SUPABASE_URL || !window.__SUPABASE_ANON_KEY) return;
    const supabase = window.supabase.createClient(window.__SUPABASE_URL, window.__SUPABASE_ANON_KEY);
    const userId = <?php echo (int)$userId; ?>;
    supabase.channel('subs-'+userId)
      .on('postgres_changes', { event: '*', schema: 'public', table: 'subscriptions', filter: 'user_id=eq.'+userId }, payload => {
        // Simply reload to reflect progress changes
        location.reload();
      })
      .subscribe();
  })();
</script>
</html>


