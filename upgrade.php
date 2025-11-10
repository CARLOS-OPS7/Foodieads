<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$pdo = getPdo();
ensureSchema($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upgrade Plan - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-7xl mx-auto px-6 py-16">
    <div class="text-center mb-6">
      <h1 class="text-4xl font-bold">Choose Your <span class="text-orange-600">Growth Plan</span></h1>
      <p class="text-slate-600 mt-3">Flexible pricing plans designed to fit restaurants of all sizes. Start with a free trial and grow your customer base today.</p>
    </div>

    <div class="flex items-center justify-center mb-8">
      <div class="inline-flex items-center space-x-3 bg-white p-1 rounded-full shadow-sm">
        <span class="text-sm text-slate-500">Monthly</span>
        <button id="billingToggle" class="relative inline-flex h-6 w-12 items-center rounded-full bg-slate-200" aria-pressed="false">
          <span class="sr-only">Toggle billing</span>
          <span id="toggleKnob" class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform"></span>
        </button>
        <span class="text-sm text-slate-500">Yearly <span class="text-emerald-600 font-medium">(save 20%)</span></span>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6 items-start">
      <!-- Starter -->
      <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-orange-50 mx-auto mb-4 text-orange-600 text-lg">
          ⭐
        </div>
        <h3 class="text-xl font-semibold text-center mb-2">Starter</h3>
        <p class="text-center text-slate-500 mb-6">Perfect for small restaurants getting started</p>
        <div class="text-center text-3xl font-extrabold text-slate-900 mb-2">KES 500<span id="starterPeriod" class="text-sm text-slate-500">/month</span></div>
        <div class="text-center text-sm text-emerald-600 mb-4">7-day free trial</div>
        <ul class="text-slate-600 text-sm space-y-2 mb-6">
          <li>Limited ad reach (local area)</li>
          <li>Basic restaurant profile</li>
          <li>Email support</li>
        </ul>
          <a href="payment.php?plan=basic" class="block bg-slate-900 text-white px-4 py-2 rounded-lg text-center cta-track" data-plan="basic">Start 7 Day Trial</a>
  <div class="mt-4 text-center text-xs text-slate-400"><a href="compare.php" class="underline">Compare plans</a></div>
      </div>

      <!-- Professional (highlighted) -->
      <div class="relative transform scale-105 z-10 bg-white border-2 border-orange-500 rounded-2xl p-8 -mx-2 shadow-2xl">
        <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-orange-500 text-white px-4 py-1 rounded-full text-sm">Most Popular</div>
        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-orange-50 mx-auto mb-4 text-orange-600 text-lg">
          ⚡
        </div>
        <h3 class="text-xl font-semibold text-center mb-2">Professional</h3>
        <p class="text-center text-slate-600 mb-6">Most popular choice for growing restaurants</p>
        <div class="text-center text-3xl font-extrabold text-slate-900 mb-2">KES 1,000<span id="proPeriod" class="text-sm text-slate-500">/month</span></div>
        <div class="text-center text-sm text-emerald-600 mb-4">14-day free trial</div>
        <ul class="text-slate-600 text-sm space-y-2 mb-6">
          <li>Moderate ad reach (city-wide)</li>
          <li>Enhanced restaurant profile</li>
          <li>Photo gallery (up to 20 photos)</li>
          <li>Priority support</li>
          <li>Advanced targeting & analytics</li>
        </ul>
          <a href="payment.php?plan=standard" class="block bg-orange-500 text-white px-4 py-2 rounded-lg text-center cta-track" data-plan="standard">Start 14 Day Trial</a>
  <div class="mt-4 text-center text-xs text-slate-500">Or <a href="contact.php" class="underline cta-contact">Contact sales</a> for an enterprise plan</div>
      </div>

      <!-- Premium -->
      <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-amber-50 mx-auto mb-4 text-amber-600 text-lg">
          👑
        </div>
        <h3 class="text-xl font-semibold text-center mb-2">Premium</h3>
        <p class="text-center text-slate-500 mb-6">Maximum visibility for established restaurants</p>
        <div class="text-center text-3xl font-extrabold text-slate-900 mb-2">KES 1,500<span id="premPeriod" class="text-sm text-slate-500">/month</span></div>
        <div class="text-center text-sm text-emerald-600 mb-4">30-day free trial</div>
        <ul class="text-slate-600 text-sm space-y-2 mb-6">
          <li>High ad reach (nationwide)</li>
          <li>Premium restaurant profile</li>
          <li>Unlimited photo gallery</li>
          <li>Dedicated account manager</li>
        </ul>
        <a href="payment.php?plan=premium" class="block bg-emerald-600 text-white px-4 py-2 rounded-lg text-center">Start 30 Day Trial</a>
      </div>
    </div>

    <div class="mt-12 text-center">
      <h2 class="text-3xl font-semibold mb-2">All Plans Include</h2>
      <p class="text-slate-500 mb-6">Essential features to help your restaurant succeed online</p>
      <div class="max-w-4xl mx-auto grid md:grid-cols-2 gap-6 text-left">
        <ul class="space-y-3 text-slate-700">
          <li>✔ Mobile-optimized restaurant listings</li>
          <li>✔ Customer inquiry management</li>
          <li>✔ Real-time performance tracking</li>
          <li>✔ Secure payment processing</li>
          <li>✔ Built-in promotions & coupons</li>
        </ul>
        <ul class="space-y-3 text-slate-700">
          <li>✔ Restaurant photo uploads</li>
          <li>✔ Menu display options</li>
          <li>✔ Location-based targeting</li>
          <li>✔ Customer support</li>
          <li>✔ Analytics dashboard</li>
        </ul>
      </div>

      <div class="mt-8 text-sm text-slate-500">
        <p>Need help choosing a plan? <a href="contact.php" class="underline">Talk to sales</a> or <a href="faq.php" class="underline">read our FAQ</a>.</p>
      </div>
    </div>

    <div class="mt-10 max-w-3xl mx-auto text-center text-xs text-slate-400">
      <p>Billing cycles are monthly by default. Yearly plans save 20% and are billed annually. All trials are available for new customers only and require a valid payment method. Cancel anytime.</p>
    </div>
  </div>
  <?php include __DIR__ . '/partials/faq.php'; ?>

  <script>
    // Billing toggle behavior — toggles displayed periods and discount note
    const toggle = document.getElementById('billingToggle');
    const knob = document.getElementById('toggleKnob');
    let yearly = false;
    function updateUI() {
      knob.style.transform = yearly ? 'translateX(1.4rem)' : 'translateX(0)';
      document.getElementById('starterPeriod').textContent = yearly ? '/year' : '/month';
      document.getElementById('proPeriod').textContent = yearly ? '/year' : '/month';
      document.getElementById('premPeriod').textContent = yearly ? '/year' : '/month';
    }
    toggle.addEventListener('click', () => { yearly = !yearly; toggle.setAttribute('aria-pressed', String(yearly)); updateUI(); });
    updateUI();
      // CTA analytics tracking
      function sendEvent(event, plan) {
        fetch('track_event.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'event=' + encodeURIComponent(event) + '&plan=' + encodeURIComponent(plan || '')
        });
      }
      document.querySelectorAll('.cta-track').forEach(function(btn) {
        btn.addEventListener('click', function() {
          sendEvent('start_trial', btn.dataset.plan);
        });
      });
      document.querySelectorAll('.cta-contact').forEach(function(link) {
        link.addEventListener('click', function() {
          sendEvent('contact_sales', '');
        });
      });
  </script>
</body>
</html>


