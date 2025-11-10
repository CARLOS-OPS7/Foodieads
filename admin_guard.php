<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();
ensureSchema($pdo);

// Ensure the designated super admin exists
ensureSuperAdmin($pdo, 'carloskarime74@gmail.com', 'Karime@0388');

// Require logged-in user
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Fetch current user
$stmt = $pdo->prepare("SELECT id, email, role, admin_pass_hash FROM users WHERE id=? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$me || $me['email'] !== 'carloskarime74@gmail.com' || $me['role'] !== 'super_admin') {
  http_response_code(403);
  echo '<div style="max-width:640px;margin:48px auto;font-family:system-ui">'
     . '<h2>Access denied</h2>'
     . '<p>Only the designated super admin can access this area.</p>'
     . '</div>';
  exit;
}

// Admin password challenge (per-session)
if (!isset($_SESSION['admin_verified']) || $_SESSION['admin_verified'] !== true) {
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['admin_password'])) {
    $ok = password_verify($_POST['admin_password'], (string)$me['admin_pass_hash']);
    if ($ok) {
      $_SESSION['admin_verified'] = true;
      header('Location: ' . ($_SERVER['REQUEST_URI'] ?? 'admin.php'));
      exit;
    }
    $err = 'Invalid admin password';
  }
  echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin Verification</title>'
     . '<script src="https://cdn.tailwindcss.com"></script>'
     . '<link rel="stylesheet" href="assets/styles.css"></head><body class="bg-slate-50">'
     . '<div class="max-w-md mx-auto px-6 py-10">'
     . '  <div class="fa-card p-6">'
     . '    <h1 class="text-xl font-semibold mb-4">Admin verification</h1>'
     . (isset($err) ? ('<div class="mb-3 text-rose-600">' . htmlspecialchars($err) . '</div>') : '')
     . '    <form method="POST">'
     . '      <input type="password" name="admin_password" placeholder="Admin password" class="w-full border border-slate-200 rounded px-3 py-2 mb-3">'
     . '      <button class="px-3 py-2 rounded bg-slate-900 text-white">Verify</button>'
     . '    </form>'
     . '  </div>'
     . '</div>'
     . '</body></html>';
  exit;
}



