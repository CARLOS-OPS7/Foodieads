<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pdo = getDBConnection();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM ads WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Ads - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  </head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold">My Ads</h2>
      <a href="create_ad.php" class="fa-btn">Create New Ad</a>
    </div>

    <?php if (count($ads) === 0): ?>
      <div class="text-center text-slate-600">You haven’t created any ads yet. <a href="create_ad.php" class="text-orange-600 font-semibold">Create one now</a> 🚀</div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($ads as $ad): ?>
          <div class="fa-card overflow-hidden">
            <?php if (!empty($ad['image'])): ?>
              <img class="w-full h-48 object-cover" src="<?php echo htmlspecialchars($ad['image']); ?>" alt="Ad Image">
            <?php else: ?>
              <img class="w-full h-48 object-cover" src="https://via.placeholder.com/600x300?text=No+Image" alt="No Image">
            <?php endif; ?>
            <div class="p-4">
              <h3 class="font-semibold"><?php echo htmlspecialchars($ad['title']); ?></h3>
              <p class="text-slate-600 text-sm mt-1"><?php echo htmlspecialchars($ad['description']); ?></p>
              <div class="flex items-center justify-between mt-3">
                <span class="px-2 py-1 rounded-md text-white text-xs bg-orange-600"><?php echo htmlspecialchars($ad['category']); ?></span>
                <span class="text-xs text-slate-500">📅 <?php echo date("M d, Y", strtotime($ad['created_at'])); ?></span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
