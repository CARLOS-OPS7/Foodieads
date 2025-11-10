<?php
// track_event.php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['error'=>'Not logged in']); exit; }
$event = $_POST['event'] ?? '';
$plan = $_POST['plan'] ?? '';
$userId = (int)$_SESSION['user_id'];
if ($event) {
    $log = __DIR__ . '/analytics.log';
    $row = date('Y-m-d H:i:s') . "\t" . $userId . "\t" . $event . "\t" . $plan . "\n";
    file_put_contents($log, $row, FILE_APPEND);
    echo json_encode(['status'=>'ok']);
} else {
    http_response_code(400); echo json_encode(['error'=>'Missing event']);
}
