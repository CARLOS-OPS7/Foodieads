<?php
require_once __DIR__ . '/admin_guard.php';
$pdo = getPdo();

// Quick stats
$pendingSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='pending'")->fetchColumn();
$activeSubs = (int)$pdo->query("SELECT COUNT(*) FROM subscriptions WHERE status='active'")->fetchColumn();
try { $approvedUsers = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE is_approved=1")->fetchColumn(); } catch (Throwable $e) { $approvedUsers = 0; }
try { $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(); } catch (Throwable $e) { $totalUsers = 0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="https://unpkg.com/htmx.org@1.9.12"></script>
  <script src="https://unpkg.com/alpinejs@3.x.x" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar_admin.php'; ?>
  <div x-data="{ tab: 'overview', sidebarOpen: false }" class="min-h-screen">
    <div class="flex">
      <aside class="fixed top-0 left-0 h-full w-64 bg-white border-r border-slate-200 z-30 transform transition-transform duration-200 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="h-16 flex items-center px-4 border-b border-slate-200">
          <span class="font-semibold">Admin</span>
        </div>
        <nav class="p-4 space-y-1">
          <button @click="tab='overview'; sidebarOpen=false" :class="tab==='overview' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">📊 Dashboard</button>
          <button @click="tab='ads'; sidebarOpen=false" :class="tab==='ads' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">✏️ Manage Ads</button>
          <button @click="tab='payments'; sidebarOpen=false" :class="tab==='payments' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">💳 Payments & Subscriptions</button>
          <div class="pt-2 border-t border-slate-200 mt-2"></div>
          <button @click="tab='subs'; sidebarOpen=false" :class="tab==='subs' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">Subscriptions</button>
          <button @click="tab='restaurants'; sidebarOpen=false" :class="tab==='restaurants' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">Restaurants</button>
          <button @click="tab='orders'; sidebarOpen=false" :class="tab==='orders' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">Orders</button>
          <button @click="tab='analytics'; sidebarOpen=false" :class="tab==='analytics' ? 'bg-slate-100' : ''" class="w-full text-left px-3 py-2 rounded transition">Analytics</button>
        </nav>
      </aside>

      <div class="flex-1 w-full lg:pl-64">
        <div class="max-w-7xl mx-auto px-6 py-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden px-3 py-2 fa-card">Menu</button>
          </div>

          <div class="grid md:grid-cols-4 gap-6 mb-8">
            <div class="fa-card p-6 text-center hover:scale-[1.01] transition">
              <div class="text-sm text-slate-500">Pending Subscriptions</div>
              <div class="text-3xl font-extrabold"><?php echo $pendingSubs; ?></div>
            </div>
            <div class="fa-card p-6 text-center hover:scale-[1.01] transition">
              <div class="text-sm text-slate-500">Active Subscriptions</div>
              <div class="text-3xl font-extrabold"><?php echo $activeSubs; ?></div>
            </div>
            <div class="fa-card p-6 text-center hover:scale-[1.01] transition">
              <div class="text-sm text-slate-500">Approved Restaurants</div>
              <div class="text-3xl font-extrabold"><?php echo $approvedUsers; ?></div>
            </div>
            <div class="fa-card p-6 text-center hover:scale-[1.01] transition">
              <div class="text-sm text-slate-500">Total Users</div>
              <div class="text-3xl font-extrabold"><?php echo $totalUsers; ?></div>
            </div>
          </div>

          <div class="fa-card p-2 mb-6">
            <div class="flex gap-2 overflow-auto">
              <button @click="tab='overview'" :class="tab==='overview' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Overview</button>
              <button @click="tab='ads'" :class="tab==='ads' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Manage Ads</button>
              <button @click="tab='payments'" :class="tab==='payments' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Payments & Subscriptions</button>
              <button @click="tab='subs'" :class="tab==='subs' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Subscriptions</button>
              <button @click="tab='restaurants'" :class="tab==='restaurants' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Restaurants</button>
              <button @click="tab='orders'" :class="tab==='orders' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Orders</button>
              <button @click="tab='analytics'" :class="tab==='analytics' ? 'bg-slate-100' : ''" class="px-3 py-2 rounded">Analytics</button>
            </div>
          </div>

          <div x-show="tab==='overview'" x-transition>
            <div class="grid md:grid-cols-2 gap-6">
              <a hx-get="admin_subscriptions.php?embed=1" hx-target="#panel" hx-swap="innerHTML" class="fa-card p-6 block hover:shadow-md">Manage Subscriptions</a>
              <a hx-get="admin_restaurants.php?embed=1" hx-target="#panel" hx-swap="innerHTML" class="fa-card p-6 block hover:shadow-md">Restaurants</a>
              <a hx-get="admin_orders.php?embed=1" hx-target="#panel" hx-swap="innerHTML" class="fa-card p-6 block hover:shadow-md">Orders</a>
              <a hx-get="admin_analytics.php?embed=1" hx-target="#panel" hx-swap="innerHTML" class="fa-card p-6 block hover:shadow-md">Analytics</a>
            </div>

            <div class="fa-card p-6 mt-6">
              <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Ad Performance</h2>
              </div>
              <div class="w-full">
                <canvas id="adPerformanceChart" height="120"></canvas>
              </div>
            </div>
          </div>

          <div id="panel" x-show="tab!=='overview'" x-transition class="mt-6"></div>

          <template x-if="tab==='ads'">
            <div hx-get="admin_ads.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='payments'">
            <div hx-get="admin_payments.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='subs'">
            <div hx-get="admin_subscriptions.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='restaurants'">
            <div hx-get="admin_restaurants.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='orders'">
            <div hx-get="admin_orders.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
          <template x-if="tab==='analytics'">
            <div hx-get="admin_analytics.php?embed=1" hx-trigger="load" hx-target="#panel" hx-swap="innerHTML"></div>
          </template>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('alpine:init', () => {
      queueMicrotask(() => {
        const ctx = document.getElementById('adPerformanceChart');
        if (!ctx || !window.Chart) return;
        const chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [
              {
                label: 'Impressions',
                data: [120, 190, 150, 220, 300, 250, 310],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.15)',
                tension: 0.35,
                fill: true
              },
              {
                label: 'Clicks',
                data: [12, 24, 18, 30, 41, 33, 45],
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.15)',
                tension: 0.35,
                fill: true
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: { beginAtZero: true }
            },
            plugins: {
              legend: { display: true }
            }
          }
        });
      });
    });
  </script>
</body>
</html>


