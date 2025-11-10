<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Compare Plans - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-5xl mx-auto px-6 py-16">
    <h1 class="text-3xl font-bold mb-8 text-center">Compare FoodieAds Plans</h1>
    <div class="overflow-x-auto">
      <table class="min-w-full border border-slate-200 rounded-lg bg-white">
        <thead>
          <tr class="bg-slate-100">
            <th class="py-3 px-4 text-left">Feature</th>
            <th class="py-3 px-4 text-center">Starter</th>
            <th class="py-3 px-4 text-center">Professional</th>
            <th class="py-3 px-4 text-center">Premium</th>
          </tr>
        </thead>
        <tbody class="text-slate-700">
          <tr><td class="py-2 px-4">Ad Reach</td><td class="text-center">Local</td><td class="text-center">City-wide</td><td class="text-center">Nationwide</td></tr>
          <tr><td class="py-2 px-4">Profile Type</td><td class="text-center">Basic</td><td class="text-center">Enhanced</td><td class="text-center">Premium</td></tr>
          <tr><td class="py-2 px-4">Photo Gallery</td><td class="text-center">None</td><td class="text-center">Up to 20</td><td class="text-center">Unlimited</td></tr>
          <tr><td class="py-2 px-4">Support</td><td class="text-center">Email</td><td class="text-center">Priority</td><td class="text-center">Dedicated Manager</td></tr>
          <tr><td class="py-2 px-4">Trial Period</td><td class="text-center">7 days</td><td class="text-center">14 days</td><td class="text-center">30 days</td></tr>
          <tr><td class="py-2 px-4">Advanced Targeting & Analytics</td><td class="text-center">-</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
          <tr><td class="py-2 px-4">Promotions & Coupons</td><td class="text-center">-</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
          <tr><td class="py-2 px-4">Menu Display</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
          <tr><td class="py-2 px-4">Mobile Listing</td><td class="text-center">✔</td><td class="text-center">✔</td><td class="text-center">✔</td></tr>
        </tbody>
      </table>
    </div>
    <div class="mt-8 text-center">
      <a href="upgrade.php" class="bg-orange-500 text-white px-6 py-2 rounded-lg font-semibold">Choose a Plan</a>
    </div>
  </div>
  <?php include __DIR__ . '/partials/faq.php'; ?>
</body>
</html>
