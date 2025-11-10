<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = (int)$_SESSION['user_id'];
$plan = $_GET['plan'] ?? 'basic';
$billingPeriod = $_GET['period'] ?? 'monthly';
$pdo = getPdo();
ensureSchema($pdo);
$info = planToAmountAndTrial($plan);
$info = planToAmountAndTrial($plan, $billingPeriod);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['method'] ?? 'mpesa';
    if ($method === 'mpesa') {
        $code = trim($_POST['mpesa_code'] ?? '');
        if ($code === '') {
            $message = '<p class="text-red-600">Please enter your M-Pesa confirmation code.</p>';
        } else {
            $sid = createPendingSubscription($pdo, $userId, $plan, 'mpesa_manual', $code);
            // Notify admin later via SMS/WhatsApp integration
            header('Location: owner_dashboard.php?status=pending&sid='.$sid);
            exit;
        }
    } elseif ($method === 'card') {
        $sid = createPendingSubscription($pdo, $userId, $plan, 'card');
        // TODO: redirect to Stripe/Flutterwave Checkout
        header('Location: owner_dashboard.php?status=pending&sid='.$sid);
        exit;
    } elseif ($method === 'paypal') {
        $sid = createPendingSubscription($pdo, $userId, $plan, 'paypal');
        // TODO: redirect to PayPal Checkout
        header('Location: owner_dashboard.php?status=pending&sid='.$sid);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Complete Payment - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="bg-slate-50">
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="max-w-3xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold mb-4">Complete Payment</h1>
    <div class="fa-card p-6">
      <p class="text-slate-700 mb-4">Plan: <strong><?php echo htmlspecialchars(ucfirst($plan)); ?></strong> • Amount: <strong>KES <?php echo $info['amount']; ?></strong> / 30 days</p>
  <p class="text-slate-700 mb-4">Plan: <strong><?php echo htmlspecialchars(ucfirst($plan)); ?></strong> • Amount: <strong>KES <?php echo $info['amount']; ?></strong> / <?php echo $billingPeriod === 'yearly' ? 'year' : 'month'; ?></p>
  $billingPeriod = $_POST['period'] ?? $billingPeriod;
            $sid = createPendingSubscription($pdo, $userId, $plan, $billingPeriod, 'mpesa_manual', $code);
  $sid = createPendingSubscription($pdo, $userId, $plan, $billingPeriod, 'card');
  $sid = createPendingSubscription($pdo, $userId, $plan, $billingPeriod, 'paypal');
      <?php echo $message; ?>
      <div class="grid md:grid-cols-3 gap-6">
        <div>
          <h3 class="font-semibold mb-2">M-Pesa (Manual)</h3>
          <p class="text-sm text-slate-600 mb-2">Send to <strong>+254115666379 (Pauline Wanjiku)</strong>. Paste the confirmation code below.</p>
          <form method="POST" class="space-y-2">
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($billingPeriod); ?>">
            <input type="hidden" name="method" value="mpesa">
            <input class="w-full border border-slate-200 rounded-lg px-3 py-2" name="mpesa_code" placeholder="e.g. QFT2X1ABC1">
            <button class="fa-btn w-full" type="submit">Submit Code</button>
          </form>
        </div>
        <div>
          <h3 class="font-semibold mb-2">Pay with Card</h3>
          <p class="text-sm text-slate-600 mb-2">Redirect to Stripe/Flutterwave Checkout.</p>
          <form method="POST">
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($billingPeriod); ?>">
            <input type="hidden" name="period" value="<?php echo htmlspecialchars($billingPeriod); ?>">
            <input type="hidden" name="method" value="card">
            <button class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 w-full" type="submit">Pay with Card</button>
          </form>
        </div>
        <div>
          <h3 class="font-semibold mb-2">PayPal (Optional)</h3>
          <p class="text-sm text-slate-600 mb-2">Redirect to PayPal Checkout.</p>
          <form method="POST">
            <input type="hidden" name="method" value="paypal">
            <button class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 w-full" type="submit">Pay with PayPal</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>


