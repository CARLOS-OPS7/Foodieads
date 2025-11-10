<footer class="bg-slate-900 text-slate-300">
  <div class="max-w-7xl mx-auto px-6 py-10 grid gap-8 md:grid-cols-4">
    <div>
      <div class="flex items-center gap-2 text-white font-semibold text-xl mb-3">
        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-gradient-to-br from-green-600 to-orange-600">🍽️</span>
        <span>Foodie<span class="text-orange-500">Ads</span></span>
      </div>
      <p class="text-sm">Helping restaurants grow their business through smart online advertising. Connect with food lovers in your area and boost your visibility.</p>
      <div class="flex items-center gap-4 mt-4 text-slate-400">
        <a href="#" aria-label="Facebook">𝕗</a>
        <a href="#" aria-label="Twitter">𝕩</a>
        <a href="#" aria-label="Instagram">◎</a>
        <a href="#" aria-label="LinkedIn">in</a>
      </div>
    </div>

    <div>
      <h4 class="font-semibold mb-3 text-white">Quick Links</h4>
      <ul class="space-y-2 text-sm">
        <li><a class="hover:text-white" href="home.php">Home</a></li>
        <li><a class="hover:text-white" href="home.php#categories">Discover Restaurants</a></li>
        <li><a class="hover:text-white" href="upgrade.php">Pricing Plans</a></li>
        <li><a class="hover:text-white" href="index.php">Register Restaurant</a></li>
        <li><a class="hover:text-white" href="contacts.php">Contact Us</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-semibold mb-3 text-white">Support</h4>
      <ul class="space-y-2 text-sm">
        <li><a class="hover:text-white" href="contacts.php">Help Center</a></li>
        <li><a class="hover:text-white" href="home.php#how">Getting Started</a></li>
        <li><a class="hover:text-white" href="home.php#faq">FAQs</a></li>
        <li><a class="hover:text-white" href="privacy.php">Privacy Policy</a></li>
        <li><a class="hover:text-white" href="terms.php">Terms of Service</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-semibold mb-3 text-white">Stay Connected</h4>
      <ul class="space-y-2 text-sm mb-4">
        <li>📞 <a class="hover:text-white" href="tel:+254115666379">+254 115666379 FOODIE</a></li>
        <li>✉️ <a class="hover:text-white" href="mailto:support@foodieads.co.ke">support@foodieads.co.ke</a></li>
        <li>📍 Westlands, Nairobi, Kenya</li>
      </ul>
      <form method="POST" action="newsletter_subscribe.php" class="flex gap-2">
        <input required type="email" name="email" placeholder="Your email" class="w-full px-3 py-2 rounded-md bg-slate-800 text-slate-100 border border-slate-700 placeholder-slate-400">
        <button class="px-4 py-2 rounded-md bg-orange-600 text-white hover:bg-orange-700" type="submit">Subscribe</button>
      </form>
      <?php if (!empty($_GET['subscribed'])): ?>
        <div class="text-green-400 text-sm mt-2">Subscribed successfully.</div>
      <?php endif; ?>
    </div>
  </div>
</footer>




