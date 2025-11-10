<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FoodieAds — Grow Your Restaurant</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script src="assets/theme.js?v=20250915" defer></script>
  <link rel="manifest" href="/Auth/manifest.json">
  <script>
  // Live announcements + config applier
  document.addEventListener('DOMContentLoaded', () => {
    const applyConfig = (cfg) => {
      if (!cfg) return;
      if (cfg.primary_color) {
        document.documentElement.style.setProperty('--fa-primary', cfg.primary_color);
        document.documentElement.style.setProperty('--fa-primary-dark', cfg.primary_color);
      }
      if (cfg.font_family) {
        document.body.style.fontFamily = cfg.font_family + ', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif';
      }
      if (cfg.layout_variant === 'compact') {
        document.body.classList.add('layout-compact');
        document.body.classList.remove('layout-spacious');
      } else if (cfg.layout_variant === 'spacious') {
        document.body.classList.add('layout-spacious');
        document.body.classList.remove('layout-compact');
      }
      if (cfg.show_extra_panel === '1') {
        const id = 'extra-panel';
        if (!document.getElementById(id)) {
          const panel = document.createElement('div');
          panel.id = id;
          panel.className = 'fa-card p-4 my-6';
          panel.innerHTML = '<h3 class="text-lg font-semibold mb-2">Extra Panel</h3><p class="text-slate-600">Enabled by admin.</p>';
          const container = document.querySelector('.fa-container') || document.body;
          container.insertBefore(panel, container.firstChild);
        }
      } else {
        const panel = document.getElementById('extra-panel');
        if (panel) panel.remove();
      }

      // Background image overlay (translucent)
      const ensureBgLayer = () => {
        let layer = document.getElementById('fa-bg-layer');
        if (!layer) {
          layer = document.createElement('div');
          layer.id = 'fa-bg-layer';
          layer.style.position = 'fixed';
          layer.style.inset = '0';
          layer.style.zIndex = '0';
          layer.style.pointerEvents = 'none';
          layer.style.backgroundRepeat = 'no-repeat';
          document.body.prepend(layer);
          document.body.style.position = 'relative';
        }
        return layer;
      };
      const bgUrl = cfg.bg_image_url;
      const bgOp = parseFloat(cfg.bg_image_opacity || '0');
      if (bgUrl) {
        const layer = ensureBgLayer();
        layer.style.backgroundImage = 'url(' + bgUrl + ')';
        layer.style.backgroundSize = 'cover';
        layer.style.backgroundPosition = 'center';
        layer.style.opacity = isNaN(bgOp) ? '0.2' : String(Math.min(Math.max(bgOp, 0), 1));
      } else {
        const layer = document.getElementById('fa-bg-layer');
        if (layer) layer.remove();
      }
    };

    const renderAnnouncements = (list) => {
      // Remove existing containers
      document.querySelectorAll('.fa-annc-banner,.fa-annc-popup,.fa-annc-slide').forEach(n => n.remove());
      if (!Array.isArray(list) || list.length===0) return;
      // Banner: show top-most banner
      const banner = list.find(a => a.kind==='banner') || list[0];
      if (banner) {
        const el = document.createElement('div');
        el.className = 'fa-annc-banner';
        el.style.cssText = 'position:sticky;top:0;z-index:40;background:#f59e0b;color:white;padding:.5rem 1rem;text-align:center';
        el.textContent = banner.content;
        document.body.prepend(el);
      }
      // Popup: first popup
      const pop = list.find(a => a.kind==='popup');
      if (pop) {
        const wrap = document.createElement('div');
        wrap.className = 'fa-annc-popup';
        wrap.style.cssText = 'position:fixed;right:1rem;bottom:1rem;z-index:50;max-width:360px';
        wrap.innerHTML = '<div class="fa-card p-4 shadow-lg"><div style="display:flex;justify-content:space-between;align-items:center"><strong>Notice</strong><button aria-label="Close" style="border:none;background:transparent;font-size:20px;line-height:1;cursor:pointer">×</button></div><div class="mt-2 text-slate-600"></div></div>';
        wrap.querySelector('.mt-2').textContent = pop.content;
        wrap.querySelector('button').addEventListener('click', () => wrap.remove());
        document.body.appendChild(wrap);
      }
      // Slide: simple top-right toast
      const slide = list.find(a => a.kind==='slide');
      if (slide) {
        const toast = document.createElement('div');
        toast.className = 'fa-annc-slide';
        toast.innerHTML = '<div class="fa-card p-3">'+ slide.content.replace(/</g,'&lt;') +'</div>';
        document.body.appendChild(toast);
        requestAnimationFrame(() => { toast.style.transform='translateY(0)'; toast.style.opacity='1'; });
        setTimeout(() => { toast.style.opacity='0'; toast.style.transform='translateY(-10px)'; setTimeout(()=>toast.remove(), 300); }, 5000);
      }
    };

    const fetchState = () => {
      fetch('site_state.php', { cache: 'no-store' })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(({ announcements, config }) => { applyConfig(config); renderAnnouncements(announcements); })
        .catch(() => {});
    };

    fetchState();
    setInterval(fetchState, 8000); // small polling for live updates
  });
  </script>
</head>
<body class="bg-white text-slate-900" data-enable-chatbot="true">
  <!-- Navbar -->
  <?php include __DIR__ . '/partials/navbar.php'; ?>

  <!-- Floating campaigns -->
  <?php
    require_once __DIR__ . '/subscriptions_helper.php';
    $pdo = getPdo();
    ensureSchema($pdo);
    $campaigns = [];
    try {
      $stmt = $pdo->query("SELECT id, title, message, image_url FROM campaigns WHERE is_active=1 ORDER BY created_at DESC LIMIT 10");
      $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) { $campaigns = []; }

    // Choose a relevant fallback image if none provided
    $autoImageFor = function(array $c): string {
      $text = strtolower(trim(($c['title'] ?? '') . ' ' . ($c['message'] ?? '')));
      $map = [
        'pizza' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=600&q=80&auto=format&fit=crop',
        'burger' => 'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=600&q=80&auto=format&fit=crop',
        'sushi' => 'https://images.unsplash.com/photo-1563379091339-03246963d4d0?w=600&q=80&auto=format&fit=crop',
        'coffee' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=600&q=80&auto=format&fit=crop',
        'chicken' => 'https://images.unsplash.com/photo-1604908176997-4316514f2c1a?w=600&q=80&auto=format&fit=crop',
        'steak' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=600&q=80&auto=format&fit=crop',
        'seafood' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80&auto=format&fit=crop',
        'dessert' => 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?w=600&q=80&auto=format&fit=crop',
        'salad' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=600&q=80&auto=format&fit=crop',
        'pasta' => 'https://images.unsplash.com/photo-1521389508051-d7ffb5dc8bbf?w=600&q=80&auto=format&fit=crop',
        'breakfast' => 'https://images.unsplash.com/photo-1508737804141-4c3b688e2546?w=600&q=80&auto=format&fit=crop',
      ];
      foreach ($map as $key => $url) {
        if ($text !== '' && strpos($text, $key) !== false) return $url;
      }
      // generic promo fallback
      return 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=600&q=80&auto=format&fit=crop';
    };
  ?>
  <?php if (!empty($campaigns)): ?>
  <div class="fixed inset-x-0 top-16 z-30 pointer-events-none">
    <div class="relative max-w-7xl mx-auto px-4">
      <div class="mb-2 flex justify-end gap-2 pointer-events-auto">
        <button class="px-2 py-1 text-xs rounded border border-slate-300 bg-white hover:bg-slate-50" onclick="(function(){try{localStorage.setItem('fa_dismissed_campaigns', JSON.stringify([])); location.reload();}catch(e){}})()">Restore hidden</button>
        <button class="px-2 py-1 text-xs rounded border border-slate-300 bg-white hover:bg-slate-50" onclick="(function(){try{const k='fa_dismissed_campaigns'; const ids=[...document.querySelectorAll('[data-campaign-id]')].map(el=>String(el.getAttribute('data-campaign-id'))); localStorage.setItem(k, JSON.stringify(ids)); document.querySelectorAll('[data-campaign-id]').forEach(el=>el.remove());}catch(e){}})()">Dismiss all</button>
      </div>
      <div class="grid gap-4 md:grid-cols-2">
        <?php foreach ($campaigns as $c): $cid = (int)$c['id']; ?>
          <div class="pointer-events-auto animate-floatXL bg-white/95 backdrop-blur rounded-2xl shadow-xl border border-slate-200 p-4 flex items-center gap-4" data-campaign-id="<?php echo $cid; ?>">
            <?php $img = !empty($c['image_url']) ? $c['image_url'] : $autoImageFor($c); ?>
            <img src="<?php echo htmlspecialchars($img); ?>" alt="" class="h-20 w-20 rounded-xl object-cover shadow">
            <div class="min-w-0 flex-1">
              <div class="font-extrabold text-slate-900 text-lg leading-tight"><?php echo htmlspecialchars($c['title']); ?></div>
              <?php if (!empty($c['message'])): ?>
                <div class="text-sm text-slate-700 mt-1 line-clamp-2"><?php echo htmlspecialchars($c['message']); ?></div>
              <?php endif; ?>
            </div>
            <button class="ml-2 text-slate-500 hover:text-slate-700 text-xl" title="Dismiss" aria-label="Dismiss" onclick="(function(btn){ try { const card = btn.closest('[data-campaign-id]'); if(!card)return; const id = card.getAttribute('data-campaign-id'); const k='fa_dismissed_campaigns'; const set = new Set(JSON.parse(localStorage.getItem(k)||'[]')); set.add(String(id)); localStorage.setItem(k, JSON.stringify(Array.from(set))); card.remove(); } catch(e){} })(this)">×</button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <style>
    @keyframes floatXL { 0%{ transform: translateY(0) scale(1) } 50%{ transform: translateY(-10px) scale(1.02) } 100%{ transform: translateY(0) scale(1) } }
    .animate-floatXL { animation: floatXL 6s ease-in-out infinite; }
    .line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden }
  </style>
  <script>
    // Hide campaigns dismissed by this user (localStorage)
    (function(){
      try {
        const k='fa_dismissed_campaigns';
        const dismissed = new Set(JSON.parse(localStorage.getItem(k)||'[]'));
        document.querySelectorAll('[data-campaign-id]').forEach(el => {
          const id = el.getAttribute('data-campaign-id');
          if (dismissed.has(String(id))) el.remove();
        });
      } catch(e){}
    })();
  </script>
  <?php endif; ?>

  <!-- Hero -->
  <section class="relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 grid lg:grid-cols-2 gap-10 items-center">
      <div>
        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight leading-tight">
          Grow Your <span class="text-slate-900">Restaurant</span> with
          <span class="bg-gradient-to-r from-green-600 to-orange-600 bg-clip-text text-transparent">Smart
          Advertising</span>
        </h1>
        <p class="mt-4 text-slate-600 max-w-xl">
          Connect with food lovers in your area. FoodieAds helps restaurant owners advertise their business online and reach customers who are hungry for great food.
        </p>
        <div class="mt-6 flex items-center gap-3">
          <a href="index.php" class="fa-btn">Start Free Trial</a>
          <a href="#categories" class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">Browse Restaurants</a>
        </div>
      </div>
      <div class="relative">
        <div class="slideshow-container" id="hero-slideshow">
          <div class="slide active">
            <img class="image-3d" src="https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=1600&q=80&auto=format&fit=crop" alt="Delicious pizza with fresh ingredients">
            <div class="slide-content">
              <h3 class="text-2xl font-bold mb-2">Authentic Italian Pizza</h3>
              <p class="text-lg opacity-90">Fresh ingredients, traditional recipes</p>
            </div>
          </div>
          <div class="slide">
            <img class="image-3d" src="https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=1600&q=80&auto=format&fit=crop" alt="Gourmet burger with premium ingredients">
            <div class="slide-content">
              <h3 class="text-2xl font-bold mb-2">Gourmet Burgers</h3>
              <p class="text-lg opacity-90">Premium beef, artisanal buns</p>
            </div>
          </div>
          <div class="slide">
            <img class="image-3d" src="https://images.unsplash.com/photo-1563379091339-03246963d4d0?w=1600&q=80&auto=format&fit=crop" alt="Fresh sushi platter">
            <div class="slide-content">
              <h3 class="text-2xl font-bold mb-2">Fresh Sushi</h3>
              <p class="text-lg opacity-90">Master-crafted, daily fresh</p>
            </div>
          </div>
          <div class="slide">
            <img class="image-3d" src="https://images.unsplash.com/photo-1551024506-0bccd828d307?w=1600&q=80&auto=format&fit=crop" alt="Artisanal coffee and pastries">
            <div class="slide-content">
              <h3 class="text-2xl font-bold mb-2">Artisanal Coffee</h3>
              <p class="text-lg opacity-90">Single-origin beans, handcrafted</p>
            </div>
          </div>
          <div class="slide-indicators"></div>
        </div>
        <div class="absolute -bottom-4 left-8 bg-white shadow-lg rounded-xl px-4 py-3 flex items-center gap-3">
          <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 font-semibold">4.9</div>
          <div>
            <div class="text-sm font-semibold">Rating</div>
            <div class="text-xs text-slate-500">From 500+ restaurants</div>
          </div>
        </div>
      </div>
    </div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-b from-transparent to-slate-50"></div>
  </section>

  <!-- Why Choose -->
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

  <!-- How it works -->
  <section id="how" class="py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
      <h2 class="text-2xl md:text-3xl font-extrabold">How FoodieAds Works</h2>
      <p class="text-slate-600 mt-2">Get started in just 3 simple steps</p>
      <div class="grid md:grid-cols-3 gap-6 mt-10">
        <div class="fa-card p-6">
          <div class="h-8 w-8 rounded-full bg-orange-100 text-orange-700 font-bold grid place-items-center mb-3">1</div>
          <h3 class="font-semibold">Register Your Restaurant</h3>
          <p class="text-sm text-slate-600 mt-1">Add details, photos, and menu info.</p>
        </div>
        <div class="fa-card p-6">
          <div class="h-8 w-8 rounded-full bg-green-100 text-green-700 font-bold grid place-items-center mb-3">2</div>
          <h3 class="font-semibold">Choose Your Plan</h3>
          <p class="text-sm text-slate-600 mt-1">Affordable pricing to fit your goals.</p>
        </div>
        <div class="fa-card p-6">
          <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-700 font-bold grid place-items-center mb-3">3</div>
          <h3 class="font-semibold">Start Getting Customers</h3>
          <p class="text-sm text-slate-600 mt-1">Appear to hungry customers nearby.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Categories -->
  <section id="categories" class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
      <h2 class="text-2xl md:text-3xl font-extrabold text-center mb-12">Perfect for Every Occasion</h2>
      <div class="grid md:grid-cols-4 gap-6">
        <div class="fa-card p-6 group hover:scale-105 transition-transform duration-300">
          <div class="relative overflow-hidden rounded-lg mb-4">
            <img class="image-3d w-full h-48 object-cover" src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80&auto=format&fit=crop" alt="Romantic candlelit dinner">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-4 left-4 text-white">
              <div class="text-2xl mb-2">💖</div>
              <h3 class="font-semibold text-lg">Romantic Dinners</h3>
            </div>
          </div>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Candlelit restaurants</li>
            <li>Rooftop dining</li>
            <li>Wine & dine venues</li>
          </ul>
        </div>
        <div class="fa-card p-6 group hover:scale-105 transition-transform duration-300">
          <div class="relative overflow-hidden rounded-lg mb-4">
            <img class="image-3d w-full h-48 object-cover" src="https://images.unsplash.com/photo-1571091718767-18b5b1457add?w=800&q=80&auto=format&fit=crop" alt="Family pizza dinner">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-4 left-4 text-white">
              <div class="text-2xl mb-2">👨‍👩‍👧‍👦</div>
              <h3 class="font-semibold text-lg">Family Treats</h3>
            </div>
          </div>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Buffet restaurants</li>
            <li>Pizza places</li>
            <li>Ice cream parlors</li>
          </ul>
        </div>
        <div class="fa-card p-6 group hover:scale-105 transition-transform duration-300">
          <div class="relative overflow-hidden rounded-lg mb-4">
            <img class="image-3d w-full h-48 object-cover" src="https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=800&q=80&auto=format&fit=crop" alt="Casual coffee shop">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-4 left-4 text-white">
              <div class="text-2xl mb-2">🗣️</div>
              <h3 class="font-semibold text-lg">Casual Hangouts</h3>
            </div>
          </div>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Coffee shops</li>
            <li>Burger joints</li>
            <li>Sports bars</li>
          </ul>
        </div>
        <div class="fa-card p-6 group hover:scale-105 transition-transform duration-300">
          <div class="relative overflow-hidden rounded-lg mb-4">
            <img class="image-3d w-full h-48 object-cover" src="https://images.unsplash.com/photo-1551024506-0bccd828d307?w=800&q=80&auto=format&fit=crop" alt="Fine dining dessert">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute bottom-4 left-4 text-white">
              <div class="text-2xl mb-2">🎉</div>
              <h3 class="font-semibold text-lg">Special Celebrations</h3>
            </div>
          </div>
          <ul class="text-sm text-slate-600 space-y-1">
            <li>Fine dining</li>
            <li>Private dining rooms</li>
            <li>Dessert cafes</li>
          </ul>
        </div>
      </div>
      <div class="text-center mt-8">
        <a href="home.php#categories" class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 inline-flex items-center gap-2">Explore All Restaurants →</a>
      </div>
    </div>
  </section>

  <!-- Delivery Options -->
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
        <div class="fa-card p-6 group hover:scale-105 transition-transform duration-300">
          <div class="relative overflow-hidden rounded-lg mb-4">
            <img class="image-3d w-full h-32 object-cover" src="https://images.unsplash.com/photo-<?php echo ['1565299624946-b28f40a0ca4b', '1551782450-a2132b4ba21d', '1563379091339-03246963d4d0', '1551024506-0bccd828d307'][array_search($p['name'], array_column($partners, 'name'))]; ?>?w=400&q=80&auto=format&fit=crop" alt="<?php echo $p['name']; ?> delivery">
            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            <div class="absolute top-2 right-2">
              <div class="h-8 w-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-sm">🏍️</div>
            </div>
            <div class="absolute bottom-2 left-2 text-white">
              <h3 class="font-semibold text-sm"><?php echo $p['name']; ?></h3>
            </div>
          </div>
          <p class="text-sm text-slate-600 mb-4">📍 <?php echo $p['city']; ?></p>
          <a href="#" class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 inline-flex items-center gap-2">Order Now ↗</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Partners CTA -->
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

  <?php include __DIR__ . '/partials/faq.php'; ?>

  <!-- Footer -->
  <footer class="bg-slate-900 text-slate-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col md:flex-row items-center justify-between gap-4">
      <div>© <?php echo date('Y'); ?> FoodieAds</div>
      <div class="flex gap-4 text-sm">
        <a class="hover:text-white" href="login.php">Login</a>
        <a class="hover:text-white" href="index.php">Register</a>
        <a class="hover:text-white" href="dashboard.php">Dashboard</a>
      </div>
    </div>
  </footer>
</body>
</html>


