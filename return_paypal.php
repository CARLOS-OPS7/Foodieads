<?php
session_start();
require_once __DIR__ . '/subscriptions_helper.php';

// Simulated PayPal return URL handler
$pdo = getPdo();
$sid = (int)($_GET['sid'] ?? 0);
$success = ($_GET['success'] ?? '0') === '1';

if ($sid > 0) {
    if ($success) activateSubscription($pdo, $sid); else failSubscription($pdo, $sid);
}

header('Location: owner_dashboard.php');
exit;




