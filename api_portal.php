<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>FoodieAds API Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="https://unpkg.com/alpinejs@3.x.x" defer></script>
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <style>
    .tab-active { background:#f1f5f9 }
    iframe { width:100%; height: calc(100vh - 200px); border:0; background:white }
  </style>
</head>
<body class="bg-slate-50" data-theme="dark">
  <?php require_once __DIR__ . '/admin_guard.php'; ?>
  <?php include __DIR__ . '/partials/navbar_admin.php'; ?>
  <div x-data="{ tab: 'dashboard', sidebarOpen: false, base: 'http://localhost:8081' }" class="min-h-screen relative">
    <div aria-hidden="true" class="pointer-events-none fixed inset-0" style="background: radial-gradient(1200px 600px at 60% -10%, rgba(34,197,94,0.15), transparent), radial-gradient(900px 500px at 20% 110%, rgba(59,130,246,0.12), transparent);"></div>
    <div class="flex">
      <aside class="fixed top-0 left-0 h-full w-64 bg-white border-r border-slate-200 z-30 transform transition-transform duration-200 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="h-16 flex items-center px-4 border-b border-slate-200">
          <span class="font-semibold">API Portal</span>
        </div>
        <nav class="p-4 space-y-1">
          <button @click="tab='dashboard'; sidebarOpen=false" :class="tab==='dashboard' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">📊 Dashboard</button>
          <button @click="tab='ads'; sidebarOpen=false" :class="tab==='ads' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">✏️ Manage Ads</button>
          <button @click="tab='payments'; sidebarOpen=false" :class="tab==='payments' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">💳 Payments & Subscriptions</button>
          <button @click="tab='campaigns'; sidebarOpen=false" :class="tab==='campaigns' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">🏷️ Campaigns</button>
          <button @click="tab='ai'; sidebarOpen=false" :class="tab==='ai' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">🤖 AI Console</button>
        </nav>
      </aside>

      <div class="flex-1 w-full lg:pl-64">
        <div class="max-w-7xl mx-auto px-6 py-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h1 class="text-2xl font-bold">FoodieAds API Portal</h1>
              <p class="text-slate-600 text-sm">Explore and test the API. Base URL <span class="font-mono" x-text="base"></span></p>
            </div>
            <div class="flex items-center gap-2">
              <input x-model="base" class="border border-slate-200 rounded px-2 py-1 text-sm w-60" placeholder="http://localhost:8080">
              <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden px-3 py-2 fa-card">Menu</button>
            </div>
          </div>

          <div x-show="tab==='dashboard'" x-transition>
            <div class="grid md:grid-cols-3 gap-6 mb-6">
              <div class="fa-card p-6">
                <div class="text-sm text-slate-500">Docs</div>
                <div class="mt-3 flex gap-2">
                  <a :href="base + '/docs'" target="_blank" class="px-3 py-2 rounded bg-indigo-600 text-white text-sm">Swagger</a>
                  <a :href="base + '/redoc'" target="_blank" class="px-3 py-2 rounded bg-slate-900 text-white text-sm">ReDoc</a>
                </div>
              </div>
              <div class="fa-card p-6">
                <div class="text-sm text-slate-500">Environment</div>
                <div class="mt-3 font-mono break-all text-slate-700" x-text="base"></div>
              </div>
              <div class="fa-card p-6">
                <div class="text-sm text-slate-500">Quick Links</div>
                <div class="mt-3 flex flex-wrap gap-2">
                  <button @click="tab='ads'" class="px-3 py-2 rounded bg-slate-100 text-sm">Ads</button>
                  <button @click="tab='payments'" class="px-3 py-2 rounded bg-slate-100 text-sm">Payments</button>
                </div>
              </div>
            </div>

            <div class="fa-card p-6">
              <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Ad Performance</h2>
              </div>
              <div class="w-full" style="height:240px">
                <canvas id="apiAdChart"></canvas>
              </div>
            </div>
          </div>

          <div id="panel" x-show="tab!=='dashboard'" x-transition class="mt-6"></div>

          <div x-effect="
            if (typeof htmx !== 'undefined') {
              if (tab === 'ads') {
                htmx.ajax('GET', 'admin_ads.php?embed=1', { target: '#panel', swap: 'innerHTML' });
              } else if (tab === 'payments') {
                htmx.ajax('GET', 'admin_payments.php?embed=1', { target: '#panel', swap: 'innerHTML' });
              } else if (tab === 'ai') {
                htmx.ajax('GET', 'portal_ai_console.php?embed=1', { target: '#panel', swap: 'innerHTML' });
              } else if (tab === 'campaigns') {
                htmx.ajax('GET', 'admin_campaigns.php?embed=1', { target: '#panel', swap: 'innerHTML' });
              }
            }
          " class="hidden"></div>

          <template x-if="tab==='ads'">
            <div hx-get="admin_ads.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='payments'">
            <div hx-get="admin_payments.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>

  <?php // Lightweight API for site state ?>
  <?php if (isset($_GET['state']) && $_GET['state']==='1') { 
    header('Content-Type: application/json');
    require_once __DIR__ . '/subscriptions_helper.php';
    $pdo = getPdo();
    $now = date('Y-m-d H:i:s');
    $ann = [];
    try {
      $stmt = $pdo->query("SELECT id, content, kind, priority FROM announcements WHERE active=1 AND (starts_at IS NULL OR starts_at<=NOW()) AND (expires_at IS NULL OR expires_at>=NOW()) ORDER BY priority DESC, id DESC LIMIT 10");
      $ann = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) { $ann = []; }
    $cfg = [];
    try {
      $stmt = $pdo->query("SELECT cfg_key, cfg_value FROM site_config");
      if ($stmt) {
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { $cfg[$row['cfg_key']] = $row['cfg_value']; }
      }
    } catch (Throwable $e) {}
    echo json_encode(['announcements'=>$ann,'config'=>$cfg]);
    exit;
  } ?>
          <template x-if="tab==='ai'">
            <div hx-get="portal_ai_console.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='campaigns'">
            <div hx-get="admin_campaigns.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('alpine:init', () => {
      queueMicrotask(() => {
        const el = document.getElementById('apiAdChart');
        if (!el || !window.Chart) return;
        new Chart(el, {
          type: 'bar',
          data: {
            labels: ['Impressions', 'Clicks', 'Customers'],
            datasets: [{
              label: 'Last 7 days',
              data: [1540, 236, 42],
              backgroundColor: ['#6366f1', '#22c55e', '#f59e0b']
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
          }
        });
      });
    });
  </script>
</body>
</html>



