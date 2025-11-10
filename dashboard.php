<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user_name'] ?? ($_SESSION['username'] ?? "Restaurant Owner");

// Sample testimonials (normally from DB)
$testimonials = [
    [
        "rating" => 5,
        "comment" => "FoodieAds helped me double my orders in just 2 weeks!",
        "name" => "James Kariuki",
        "restaurant" => "Nairobi Bites"
    ],
    [
        "rating" => 4,
        "comment" => "Affordable ads that actually bring customers. Highly recommended.",
        "name" => "Sarah Wambui",
        "restaurant" => "Mombasa Grill"
    ],
    [
        "rating" => 5,
        "comment" => "Easy to use and effective. My restaurant is growing faster than ever.",
        "name" => "Ali Mwangi",
        "restaurant" => "Eldoret Eats"
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-white text-slate-900 font-sans">
  <?php include __DIR__ . '/partials/navbar.php'; ?>

  <section class="relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20 grid lg:grid-cols-2 gap-10 items-center">
      <div>
        <h2 class="text-3xl md:text-4xl font-extrabold tracking-tight leading-tight">
          Welcome back, <?php echo htmlspecialchars($username); ?> —
          <span class="bg-gradient-to-r from-green-600 to-orange-600 bg-clip-text text-transparent">let’s grow your restaurant</span>
        </h2>
        <p class="mt-4 text-slate-600 max-w-xl">Manage your ads, track results, and reach more hungry customers nearby with smart, targeted campaigns.</p>
        <div class="mt-6 flex items-center gap-3">
          <a href="create_ad.php" class="fa-btn">Create New Ad</a>
          <a href="view_ads.php" class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">View My Ads</a>
        </div>
      </div>
      <div class="relative">
        <img class="w-full h-80 object-cover rounded-2xl shadow-xl" src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600&q=80&auto=format&fit=crop" alt="Burger and fries">
        <div class="absolute -bottom-4 left-8 bg-white shadow-lg rounded-xl px-4 py-3 flex items-center gap-3">
          <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-semibold">4.9</div>
          <div>
            <div class="text-sm font-semibold">Average Campaign Rating</div>
            <div class="text-xs text-slate-500">From 500+ restaurants</div>
          </div>
        </div>
      </div>
    </div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-b from-transparent to-slate-50"></div>
  </section>

  <section id="features" class="bg-slate-50 py-16">
    <div class="max-w-6xl mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-2xl md:text-3xl font-extrabold">Why Choose FoodieAds?</h2>
        <p class="text-slate-600 mt-2">Everything you need to grow your online presence and attract more customers.</p>
      </div>
      <div class="grid gap-6 md:grid-cols-3">
        <div class="fa-card p-6">
          <div class="h-12 w-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-4">🎯</div>
          <h3 class="font-semibold mb-1">Targeted Advertising</h3>
          <p class="text-sm text-slate-600">Reach local food lovers with precision‑targeted ads.</p>
        </div>
        <div class="fa-card p-6">
          <div class="h-12 w-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-4">📈</div>
          <h3 class="font-semibold mb-1">Boost Visibility</h3>
          <p class="text-sm text-slate-600">Increase your restaurant’s online presence and attract more customers.</p>
        </div>
        <div class="fa-card p-6">
          <div class="h-12 w-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-4">📊</div>
          <h3 class="font-semibold mb-1">Customer Analytics</h3>
          <p class="text-sm text-slate-600">Track performance and understand customer engagement.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="how" class="py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h2 class="text-2xl md:text-3xl font-extrabold">How FoodieAds Works</h2>
      <p class="text-slate-600 mt-2">Get started in just 3 simple steps</p>
      <div class="grid md:grid-cols-3 gap-6 mt-10">
        <div class="fa-card p-6">
          <div class="h-8 w-8 rounded-full bg-orange-100 text-orange-700 font-bold grid place-items-center mb-3">1</div>
          <h3 class="font-semibold">Create your restaurant profile</h3>
          <p class="text-sm text-slate-600 mt-1">Add details, photos, and menu info.</p>
        </div>
        <div class="fa-card p-6">
          <div class="h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold grid place-items-center mb-3">2</div>
          <h3 class="font-semibold">Choose your plan</h3>
          <p class="text-sm text-slate-600 mt-1">Affordable pricing to fit your goals.</p>
        </div>
        <div class="fa-card p-6">
          <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-700 font-bold grid place-items-center mb-3">3</div>
          <h3 class="font-semibold">Launch campaigns</h3>
          <p class="text-sm text-slate-600 mt-1">Reach hungry customers nearby.</p>
        </div>
      </div>
    </div>
  </section>

  <section id="categories" class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl md:text-3xl font-extrabold text-center mb-12">Perfect for Every Occasion</h2>
      <div class="grid md:grid-cols-4 gap-6">
        <div class="fa-card p-6">
          <div class="text-2xl mb-3">💖</div>
          <h3 class="font-semibold">Romantic Dinners</h3>
          <ul class="mt-2 text-sm text-slate-600 space-y-1">
            <li>Candlelit restaurants</li>
            <li>Rooftop dining</li>
            <li>Wine & dine venues</li>
          </ul>
        </div>
        <div class="fa-card p-6">
          <div class="text-2xl mb-3">👨‍👩‍👧‍👦</div>
          <h3 class="font-semibold">Family Treats</h3>
          <ul class="mt-2 text-sm text-slate-600 space-y-1">
            <li>Buffet restaurants</li>
            <li>Pizza places</li>
            <li>Ice cream parlors</li>
          </ul>
        </div>
        <div class="fa-card p-6">
          <div class="text-2xl mb-3">🗣️</div>
          <h3 class="font-semibold">Casual Hangouts</h3>
          <ul class="mt-2 text-sm text-slate-600 space-y-1">
            <li>Coffee shops</li>
            <li>Burger joints</li>
            <li>Sports bars</li>
          </ul>
        </div>
        <div class="fa-card p-6">
          <div class="text-2xl mb-3">🎉</div>
          <h3 class="font-semibold">Special Celebrations</h3>
          <ul class="mt-2 text-sm text-slate-600 space-y-1">
            <li>Fine dining</li>
            <li>Private dining rooms</li>
            <li>Dessert cafes</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <section class="bg-slate-50 py-16">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl md:text-3xl font-extrabold text-center">Food Delivery Options</h2>
      <p class="text-slate-600 text-center mt-2">Get your favorites delivered through our partners</p>
      <div class="grid md:grid-cols-4 gap-6 mt-10">
        <?php
          $partners = [
            ["name"=>"Glovo","city"=>"Nairobi, Mombasa, Major Towns"],
            ["name"=>"Bolt Food","city"=>"Nairobi, Mombasa"],
            ["name"=>"Uber Eats","city"=>"Nairobi, Mombasa"],
            ["name"=>"InstaPilau","city"=>"Nationwide"],
          ];
          foreach ($partners as $p):
        ?>
        <div class="fa-card p-6">
          <div class="h-12 w-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4">🏍️</div>
          <h3 class="font-semibold mb-1"><?php echo $p['name']; ?></h3>
          <p class="text-sm text-slate-600 mb-4">📍 <?php echo $p['city']; ?></p>
          <a href="#" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 inline-flex items-center gap-2">Order Now ↗</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section id="partners" class="py-16">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl md:text-3xl font-extrabold text-center">Partner with Delivery Services</h2>
      <p class="text-slate-600 text-center mt-2">Expand your reach by partnering with leading delivery platforms.</p>
      <div class="grid md:grid-cols-3 gap-6 mt-10">
        <div class="fa-card p-6">
          <h3 class="font-semibold mb-2">Glovo</h3>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Wide customer base</li>
            <li>Marketing support</li>
            <li>Easy setup</li>
          </ul>
          <a class="fa-btn mt-4" href="#">Add your restaurant</a>
        </div>
        <div class="fa-card p-6">
          <h3 class="font-semibold mb-2">Bolt Food</h3>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Quick onboarding</li>
            <li>Competitive rates</li>
            <li>Analytics</li>
          </ul>
          <a class="fa-btn mt-4" href="#">Add your restaurant</a>
        </div>
        <div class="fa-card p-6">
          <h3 class="font-semibold mb-2">Uber Eats</h3>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Global reach</li>
            <li>Professional support</li>
            <li>Marketing tools</li>
          </ul>
          <a class="fa-btn mt-4" href="#">Add your restaurant</a>
        </div>
      </div>
    </div>
  </section>

</body>
</html>
