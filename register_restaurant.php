<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Your Restaurant | FoodieAds</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-center mb-2">Register Your <span class="text-orange-600">Restaurant</span></h1>
    <p class="text-center text-slate-600 mb-8">Join hundreds of successful restaurants on FoodieAds. Fill out the form below to get started with your free trial.</p>
    <form class="space-y-8">
      <!-- Restaurant Information -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="font-semibold text-lg mb-1 flex items-center gap-2"><span class="text-orange-500">★</span> Restaurant Information</div>
        <p class="text-slate-500 text-sm mb-4">Tell us about your restaurant</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium mb-1">Restaurant Name *</label>
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="name" required placeholder="e.g., Mama's Kitchen">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Cuisine Type *</label>
            <select class="w-full border border-slate-200 rounded-lg px-3 py-2" name="cuisine" required>
              <option value="">Select cuisine type</option>
              <option>Kenyan</option><option>Italian</option><option>Chinese</option><option>Indian</option><option>Mexican</option><option>Other</option>
            </select>
          </div>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Restaurant Description *</label>
          <textarea class="w-full border border-slate-200 rounded-lg px-3 py-2" name="description" rows="2" required placeholder="Describe your restaurant, specialties, ambiance..."></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Website (Optional)</label>
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="website" placeholder="https://yourrestaurant.com">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Opening Hours</label>
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="hours" placeholder="e.g., Mon-Sun 9AM-10PM">
          </div>
        </div>
      </div>
      <!-- Contact Information -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="font-semibold text-lg mb-1 flex items-center gap-2"><span class="text-green-500">📞</span> Contact Information</div>
        <p class="text-slate-500 text-sm mb-4">How customers can reach you</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">Owner/Manager Name *</label>
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="owner" required placeholder="Full name">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">Email Address *</label>
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="email" type="email" required placeholder="your@email.com">
          </div>
        </div>
        <div class="mt-4">
          <label class="block text-sm font-medium mb-1">Phone Number *</label>
          <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="phone" required placeholder="+254 700 000 000">
        </div>
      </div>
      <!-- Location Details -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="font-semibold text-lg mb-1 flex items-center gap-2"><span class="text-blue-500">📍</span> Location Details</div>
        <p class="text-slate-500 text-sm mb-4">Where is your restaurant located?</p>
        <div class="mb-4">
          <label class="block text-sm font-medium mb-1">Street Address *</label>
          <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="address" required placeholder="Building name, street name, area">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1">City/Town *</label>
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="city" required placeholder="e.g., Nairobi">
          </div>
          <div>
            <label class="block text-sm font-medium mb-1">County *</label>
            <select class="w-full border border-slate-200 rounded-lg px-3 py-2" name="county" required>
              <option value="">Select county</option>
              <option>Nairobi</option><option>Mombasa</option><option>Kisumu</option><option>Nakuru</option><option>Eldoret</option><option>Other</option>
            </select>
          </div>
        </div>
      </div>
      <!-- Choose Your Plan -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="font-semibold text-lg mb-1">Choose Your Plan</div>
        <p class="text-slate-500 text-sm mb-4">Select the advertising plan that fits your needs</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <label class="flex flex-col border rounded-lg p-4 cursor-pointer">
            <input type="radio" name="plan" value="starter" class="mb-2" required>
            <span class="font-semibold">Starter Plan</span>
            <span class="text-orange-600 font-bold text-lg">KES 500/month</span>
            <span class="text-xs text-slate-500">7-day free trial</span>
          </label>
          <label class="flex flex-col border-2 border-orange-500 rounded-lg p-4 cursor-pointer relative">
            <input type="radio" name="plan" value="professional" class="mb-2" required>
            <span class="font-semibold">Professional Plan</span>
            <span class="text-orange-600 font-bold text-lg">KES 1,000/month</span>
            <span class="text-xs text-slate-500">14-day free trial</span>
            <span class="absolute top-2 right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded">Popular</span>
          </label>
          <label class="flex flex-col border rounded-lg p-4 cursor-pointer">
            <input type="radio" name="plan" value="premium" class="mb-2" required>
            <span class="font-semibold">Premium Plan</span>
            <span class="text-green-600 font-bold text-lg">KES 1,500/month</span>
            <span class="text-xs text-slate-500">30-day free trial</span>
          </label>
        </div>
      </div>
      <!-- Restaurant Photos -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="font-semibold text-lg mb-1 flex items-center gap-2"><span class="text-purple-500">📷</span> Restaurant Photos</div>
        <p class="text-slate-500 text-sm mb-4">Upload photos of your restaurant, food, and ambiance (you can add more later)</p>
        <input type="file" name="photos[]" multiple accept="image/*" class="block w-full border border-slate-200 rounded-lg px-3 py-2">
      </div>
      <div class="text-center pt-4">
        <button type="submit" class="fa-btn w-full max-w-xs">Register Restaurant</button>
      </div>
    </form>
  </div>
</body>
</html>
