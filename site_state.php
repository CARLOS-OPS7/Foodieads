<?php
header('Content-Type: application/json');
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();

$ann = [];
try {
  $stmt = $pdo->query("SELECT id, content, kind, priority FROM announcements WHERE active=1 AND (starts_at IS NULL OR starts_at<=NOW()) AND (expires_at IS NULL OR expires_at>=NOW()) ORDER BY priority DESC, id DESC LIMIT 10");
  $ann = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) { $ann = []; }

$cfg = [];
try {
  $stmt = $pdo->query("SELECT cfg_key, cfg_value FROM site_config");
  if ($stmt) {
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) { $cfg[$row['cfg_key']] = $row['cfg_value']; }
  }
} catch (Throwable $e) {}

echo json_encode(['announcements'=>$ann,'config'=>$cfg]);
exit;
?>


