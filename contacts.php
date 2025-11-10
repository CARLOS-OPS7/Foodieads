<?php
session_start();
require_once __DIR__ . '/db.php';
$pdo = getDBConnection();

// Ensure table for messages
try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    phone VARCHAR(40) NULL,
    category VARCHAR(60) NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {}

$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullName = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $category = trim($_POST['category'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if ($fullName && filter_var($email, FILTER_VALIDATE_EMAIL) && $subject && $message) {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (full_name, email, phone, category, subject, message) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$fullName, $email, $phone, $category, $subject, $message]);
    $notice = '<p class="text-green-600">Thanks! Your message has been sent. We\'ll get back to you soon.</p>';
  } else {
    $notice = '<p class="text-red-600">Please fill in required fields correctly.</p>';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>

  <section class="bg-gradient-to-b from-orange-50 to-transparent">
    <div class="max-w-4xl mx-auto px-6 py-14 text-center">
      <h1 class="text-4xl font-extrabold">Get in <span class="text-orange-600">Touch</span></h1>
      <p class="text-slate-600 mt-3">Have questions about FoodieAds? Need help with your restaurant listing? We're here to help you grow your business.</p>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-6 pb-16 grid md:grid-cols-3 gap-6">
    <!-- Form -->
    <div class="md:col-span-2 fa-card p-6">
      <h2 class="text-xl font-semibold mb-2">Send us a Message</h2>
      <p class="text-slate-600 mb-4 text-sm">Fill out the form below and we'll get back to you as soon as possible.</p>
      <?php echo $notice; ?>
      <form method="POST" class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm mb-1">Full Name *</label>
          <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="full_name" required placeholder="Your full name">
        </div>
        <div>
          <label class="block text-sm mb-1">Email Address *</label>
          <input type="email" class="w-full border border-slate-200 rounded-lg px-3 py-2" name="email" required placeholder="your@email.com">
        </div>
        <div>
          <label class="block text-sm mb-1">Phone Number</label>
          <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="phone" placeholder="+254 700 000 000">
        </div>
        <div>
          <label class="block text-sm mb-1">Category</label>
          <select class="w-full border border-slate-200 rounded-lg px-3 py-2" name="category">
            <option value="General">General</option>
            <option value="Billing">Billing</option>
            <option value="Technical">Technical</option>
            <option value="Partnerships">Partnerships</option>
          </select>
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm mb-1">Subject *</label>
          <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="subject" required placeholder="Brief description of your inquiry">
        </div>
        <div class="sm:col-span-2">
          <label class="block text-sm mb-1">Message *</label>
          <textarea rows="6" class="w-full border border-slate-200 rounded-lg px-3 py-2" name="message" required placeholder="Please provide details about your inquiry"></textarea>
        </div>
        <div class="sm:col-span-2">
          <button class="fa-btn w-full" type="submit">Send Message</button>
        </div>
      </form>
    </div>

    <!-- Contact Info -->
    <aside class="fa-card p-6">
      <h2 class="text-xl font-semibold mb-2">Contact Information</h2>
      <p class="text-slate-600 text-sm mb-4">Choose your preferred way to reach us</p>
      <div class="space-y-4 text-sm">
        <div class="flex gap-3">
          <div class="h-9 w-9 rounded-xl bg-green-50 text-green-700 grid place-items-center">📞</div>
          <div>
            <div class="font-semibold">Phone Support</div>
            <div>+254115666379</div>
            <div class="text-slate-500 text-xs">Mon-Fri 8AM-8PM</div>
          </div>
        </div>
        <div class="flex gap-3">
          <div class="h-9 w-9 rounded-xl bg-blue-50 text-blue-700 grid place-items-center">✉️</div>
          <div>
            <div class="font-semibold">Email Support</div>
            <div>help.foodieads@gmail.com</div>
            <div class="text-slate-500 text-xs">Response within 24 hours</div>
          </div>
        </div>
        <div class="flex gap-3">
          <div class="h-9 w-9 rounded-xl bg-purple-50 text-purple-700 grid place-items-center">💬</div>
          <div>
            <div class="font-semibold">Live Chat</div>
            <div>Available on website</div>
            <div class="text-slate-500 text-xs">Mon-Fri 9AM-6PM</div>
          </div>
        </div>
      </div>
    </aside>
  </div>
  <?php include __DIR__ . '/partials/faq.php'; ?>
</body>
</html>


