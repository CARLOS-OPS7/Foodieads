<?php
// Stub webhook endpoint for Stripe/Flutterwave
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();

// In production, verify signatures from the provider here
$input = json_decode(file_get_contents('php://input'), true) ?: [];
$subscriptionId = (int)($input['subscription_id'] ?? 0);
$status = $input['status'] ?? 'failed';

if ($subscriptionId > 0 && $status === 'successful') {
    activateSubscription($pdo, $subscriptionId);
}

http_response_code(200);
echo json_encode(['ok' => true]);




