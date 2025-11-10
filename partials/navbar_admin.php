<?php
// Minimal admin-only navbar
?>
<header class="bg-white/90 backdrop-blur border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
    <a href="index.php" class="flex items-center gap-2 font-semibold text-lg">
      <span class="inline-flex items-center justify-center h-7 w-7 rounded-lg bg-gradient-to-br from-green-600 to-orange-600 text-white">🛠️</span>
      <span>FoodieAds Admin</span>
    </a>
    <nav class="flex items-center gap-3">
      <a class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50" href="api_portal.php">API Portal</a>
      <a class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50" href="dashboard.php">User Dashboard</a>
      <a class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50" href="logout.php">Logout</a>
    </nav>
  </div>
  </header>


