<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();
ensureSchema($pdo);
$rev = listRevenueByMonth($pdo);
$regs = listRegistrationsByMonth($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin • Analytics</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50">
  <?php if (!(isset($_GET['embed']) && $_GET['embed'] == '1')) include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-6xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-6">Analytics Dashboard</h1>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="fa-card p-6">
        <h3 class="font-semibold mb-2">Monthly Revenue (KES)</h3>
        <canvas id="revChart" height="160"></canvas>
      </div>
      <div class="fa-card p-6">
        <h3 class="font-semibold mb-2">New Registrations</h3>
        <canvas id="regChart" height="160"></canvas>
      </div>
    </div>
  </div>
  <script>
    const revLabels = <?php echo json_encode(array_column($rev,'ym')); ?>;
    const revData = <?php echo json_encode(array_map('intval', array_column($rev,'revenue'))); ?>;
    const regLabels = <?php echo json_encode(array_column($regs,'ym')); ?>;
    const regData = <?php echo json_encode(array_map('intval', array_column($regs,'total'))); ?>;

    new Chart(document.getElementById('revChart'), {
      type: 'line',
      data: { labels: revLabels, datasets: [{ label: 'Revenue', data: revData, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.15)', tension: 0.3 }] },
      options: { scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('regChart'), {
      type: 'bar',
      data: { labels: regLabels, datasets: [{ label: 'Registrations', data: regData, backgroundColor: '#16a34a' }] },
      options: { scales: { y: { beginAtZero: true } } }
    });
  </script>
</body>
</html>


