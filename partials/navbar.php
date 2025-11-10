<?php
$path = basename($_SERVER['PHP_SELF']);
function active($p, $curr) { return $p === $curr ? 'text-slate-900 after:block after:h-0.5 after:bg-orange-600 after:rounded after:mt-1' : 'text-slate-600 hover:text-slate-900'; }
?>
<header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
    <a href="home.php" class="flex items-center gap-2 font-semibold text-xl">
      <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gradient-to-br from-green-600 to-orange-600 text-white">🍽️</span>
      <span>Foodie<span class="text-orange-600">Ads</span></span>
    </a>
    <nav class="hidden md:flex items-center gap-6">
      <a class="<?= active('home.php', $path) ?>" href="home.php">Home</a>
      <a class="<?= active('#discover', '') ?>" href="home.php#categories">Discover</a>
      <a class="<?= active('upgrade.php', $path) ?>" href="upgrade.php">Pricing</a>
      <a class="<?= active('contacts.php', $path) ?>" href="contacts.php">Contact</a>
      <a class="<?= active('admin.php', $path) ?>" href="admin.php">Admin</a>
    </nav>
    <div class="flex items-center gap-2">
  <button id="pwaInstallBtn" style="display:inline-flex;" class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50">📱 Install</button>
      <button id="theme-toggle" class="hidden md:inline-flex items-center px-2 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors" title="Toggle theme">
        <span id="theme-icon">☀️</span>
      </button>
      <a class="hidden md:inline-flex items-center px-3 py-2 rounded-lg border border-slate-200" href="login.php">Login</a>
      <a class="hidden md:inline-flex items-center px-3 py-2 rounded-lg border border-slate-200" href="index.php">Sign Up</a>
      <a class="px-3 py-2 rounded-lg text-white bg-orange-600 hover:bg-orange-700" href="index.php">Register Restaurant</a>
    </div>
  </div>
</header>

<script>
  // PWA install handling: show install button when available
  let deferredPrompt = null;
  const pwaBtn = document.getElementById('pwaInstallBtn');

  // Listen for the install prompt event and keep it for later
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    // show button (already visible by default)
    if (pwaBtn) pwaBtn.style.display = 'inline-flex';
  });

  if (pwaBtn) {
    pwaBtn.addEventListener('click', async () => {
      try {
        if (deferredPrompt) {
          deferredPrompt.prompt();
          const choice = await deferredPrompt.userChoice;
          deferredPrompt = null;
          // hide after user choice
          pwaBtn.style.display = 'none';
          return;
        }
        // fallback: attempt to download manifest so user can inspect/install manually
        const a = document.createElement('a');
        a.href = '/manifest.json';
        a.download = 'manifest.json';
        document.body.appendChild(a);
        a.click();
        a.remove();
      } catch (err) {
        console.error('PWA install error', err);
      }
    });
  }

  // register service worker if available and not registered yet
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistration().then(reg => {
      if (!reg) {
        navigator.serviceWorker.register('/service-worker.js').catch(err => console.error('sw register', err));
      }
    }).catch(err => console.error('sw getReg', err));
  }

  // hide install button after app is installed
  window.addEventListener('appinstalled', () => {
    if (pwaBtn) pwaBtn.style.display = 'none';
  });
</script>


