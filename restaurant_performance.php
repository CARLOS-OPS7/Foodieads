<?php
session_start();
require_once __DIR__ . '/db.php';
$pdo = getDBConnection();

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];

// Demo metrics table
try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS ad_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    impressions INT NOT NULL DEFAULT 0,
    clicks INT NOT NULL DEFAULT 0,
    spend INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(impressions),0) AS impressions, COALESCE(SUM(clicks),0) AS clicks, COALESCE(SUM(spend),0) AS spend FROM ad_metrics WHERE user_id=?");
$stmt->execute([$userId]);
$tot = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['impressions'=>0,'clicks'=>0,'spend'=>0];
$ctr = ($tot['impressions'] > 0) ? round(($tot['clicks']/$tot['impressions'])*100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Performance - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-6">Performance Metrics</h1>
    <div class="grid md:grid-cols-4 gap-6">
      <div class="fa-card p-6 text-center">
        <div class="text-sm text-slate-500">Impressions</div>
        <div class="text-2xl font-extrabold"><?php echo (int)$tot['impressions']; ?></div>
      </div>
      <div class="fa-card p-6 text-center">
        <div class="text-sm text-slate-500">Clicks</div>
        <div class="text-2xl font-extrabold"><?php echo (int)$tot['clicks']; ?></div>
      </div>
      <div class="fa-card p-6 text-center">
        <div class="text-sm text-slate-500">CTR</div>
        <div class="text-2xl font-extrabold"><?php echo $ctr; ?>%</div>
      </div>
      <div class="fa-card p-6 text-center">
        <div class="text-sm text-slate-500">Spend</div>
        <div class="text-2xl font-extrabold">KES <?php echo (int)$tot['spend']; ?></div>
      </div>
    </div>
    <div class="mt-6">
      <a class="fa-btn" href="dashboard.php">Back to Dashboard</a>
    </div>
  </div>
</body>
</html>


