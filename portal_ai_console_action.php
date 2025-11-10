<?php
require_once __DIR__ . '/subscriptions_helper.php';
$pdo = getPdo();
ensureSchema($pdo);

header('Content-Type: text/html; charset=utf-8');

// Resolve current user id from session or create/fallback
if (!function_exists('resolveCurrentUserId')) {
  function resolveCurrentUserId(PDO $pdo): int {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      @session_start();
    }
    $userId = 0;
    if (!empty($_SESSION['user_id'])) {
      $userId = (int)$_SESSION['user_id'];
      try {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE id=?');
        $stmt->execute([$userId]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
          return $userId;
        }
      } catch (Throwable $e) {}
    }
    try {
      $row = $pdo->query('SELECT id FROM users ORDER BY id ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
      if ($row && isset($row['id'])) {
        return (int)$row['id'];
      }
    } catch (Throwable $e) {}
    try {
      $email = 'console_user_' . bin2hex(random_bytes(4)) . '@example.com';
      $hash = password_hash('console-temp', PASSWORD_DEFAULT);
      $ins = $pdo->prepare('INSERT INTO users (name, email, password, is_approved, role) VALUES (?,?,?,?,\'user\')');
      $ins->execute(['Console User', $email, $hash, 1]);
      return (int)$pdo->lastInsertId();
    } catch (Throwable $e) {
      return 0;
    }
  }
}

// Ensure the ads table has the expected columns even if it already existed
if (!function_exists('ensureAdsSchemaLocal')) {
  function ensureAdsSchemaLocal(PDO $pdo): void {
    try {
      $pdo->exec("CREATE TABLE IF NOT EXISTS ads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        title VARCHAR(255) NOT NULL,
        status VARCHAR(32) NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {}
    try {
      $hasUser = $pdo->query("SHOW COLUMNS FROM ads LIKE 'user_id'")->fetch(PDO::FETCH_ASSOC);
      if (!$hasUser) {
        $pdo->exec("ALTER TABLE ads ADD COLUMN user_id INT NULL");
      }
    } catch (Throwable $e) {}
    // Best-effort add FK; ignore if already exists
    try {
      $pdo->exec("ALTER TABLE ads ADD CONSTRAINT ads_ibfk_1 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
    } catch (Throwable $e) {}
    try {
      $hasStatus = $pdo->query("SHOW COLUMNS FROM ads LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
      if (!$hasStatus) {
        $pdo->exec("ALTER TABLE ads ADD COLUMN status VARCHAR(32) NOT NULL DEFAULT 'draft'");
      }
    } catch (Throwable $e) {}
    try {
      $hasCreated = $pdo->query("SHOW COLUMNS FROM ads LIKE 'created_at'")->fetch(PDO::FETCH_ASSOC);
      if (!$hasCreated) {
        $pdo->exec("ALTER TABLE ads ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
      }
    } catch (Throwable $e) {}
  }
}

$cmd = trim($_POST['command'] ?? '');
if ($cmd === '') {
  echo '<div class="text-slate-600">No command provided.</div>';
  exit;
}

// Extremely simple intent parser; expand safely as needed
$lower = strtolower($cmd);

try {
  // Heuristic early path: if it says create/add campaign, ensure we create a campaign even if regex parsing fails
  if (preg_match("/(^|\b)(create|add)\s+campaign(\b|\s)/i", $cmd)) {
    // Extract title
    $title = '';
    if (preg_match('/campaign\s+\"([^\"]+)\"/i', $cmd, $mm)) { $title = trim($mm[1]); }
    elseif (preg_match("/campaign\s+'([^']+)'/i", $cmd, $mm)) { $title = trim($mm[1]); }
    else {
      if (preg_match("/campaign\s+(.+?)(?=\s+message|\s+image|$)/i", $cmd, $mm)) { $title = trim($mm[1]); }
    }
    $message = '';
    if (preg_match('/message\s+\"([^\"]*)\"/i', $cmd, $mm)) { $message = $mm[1]; }
    elseif (preg_match("/message\s+'([^']*)'/i", $cmd, $mm)) { $message = $mm[1]; }
    elseif (preg_match("/message\s+(.+?)(?=\s+image|$)/i", $cmd, $mm)) { $message = trim($mm[1]); }
    $imageUrl = '';
    if (preg_match('/image\s+(\S+)/i', $cmd, $mm)) { $imageUrl = trim($mm[1]); }

    if ($title === '') { $title = 'Untitled Campaign'; }
    ensureSchema($pdo);
    $stmt = $pdo->prepare("INSERT INTO campaigns (title, message, image_url, is_active) VALUES (?,?,?,1)");
    $stmt->execute([$title, $message !== '' ? $message : null, $imageUrl !== '' ? $imageUrl : null]);
    $id = (int)$pdo->lastInsertId();
    echo '<div class="text-green-700">Campaign created #' . $id . ': ' . htmlspecialchars($title) . '</div>';
    echo '<div class="mt-2">'
       . '<button class="px-2 py-1 text-sm bg-rose-600 text-white rounded" '
       . 'hx-post="portal_ai_console_action.php" '
       . 'hx-vals=' . json_encode(['command' => 'delete campaign #' . $id]) . ' '
       . 'hx-target="#ai-result" hx-swap="innerHTML">Delete</button>'
       . '</div>';
    exit;
  }

  // Create campaign: more strict parser (quoted and unquoted)
  if (preg_match(
        "/^(create|add)\s+campaign\s+(?:\"([^\"]+)\"|'([^']+)'|(.+?))(?:\s+message\s+(?:\"([^\"]+)\"|'([^']+)'|(.+?)))?(?:\s+image\s+(\S+))?\s*$/i",
        $cmd,
        $m
      )) {
    $title = '';
    if (!empty($m[2])) { $title = $m[2]; }
    elseif (!empty($m[3])) { $title = $m[3]; }
    else { $title = $m[4] ?? ''; }

    $message = '';
    if (!empty($m[5])) { $message = $m[5]; }
    elseif (!empty($m[6])) { $message = $m[6]; }
    else { $message = trim($m[7] ?? ''); }

    $imageUrl = trim($m[8] ?? '');
    ensureSchema($pdo);
    $stmt = $pdo->prepare("INSERT INTO campaigns (title, message, image_url, is_active) VALUES (?,?,?,1)");
    $stmt->execute([$title, $message, $imageUrl ?: null]);
    $id = (int)$pdo->lastInsertId();
    echo '<div class="text-green-700">Campaign created #' . $id . ': ' . htmlspecialchars($title) . '</div>';
    echo '<div class="mt-2">'
       . '<button class="px-2 py-1 text-sm bg-rose-600 text-white rounded" '
       . 'hx-post="portal_ai_console_action.php" '
       . 'hx-vals=' . json_encode(['command' => 'delete campaign #' . $id]) . ' '
       . 'hx-target="#ai-result" hx-swap="innerHTML">Delete</button>'
       . '</div>';
    exit;
  }
  // Ensure tables for announcements and site configuration
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS announcements (
      id INT AUTO_INCREMENT PRIMARY KEY,
      content TEXT NOT NULL,
      kind ENUM('banner','popup','slide') NOT NULL DEFAULT 'banner',
      active TINYINT(1) NOT NULL DEFAULT 1,
      priority INT NOT NULL DEFAULT 0,
      starts_at DATETIME NULL,
      expires_at DATETIME NULL,
      created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  } catch (Throwable $e) {}
  try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_config (
      cfg_key VARCHAR(100) PRIMARY KEY,
      cfg_value TEXT NULL,
      updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  } catch (Throwable $e) {}

  // Admin intents for announcements/config
  if (preg_match('/^(announce|offer|discount|notice|promo)\b/i', $cmd)) {
    $kind = (stripos($lower, 'popup') !== false) ? 'popup' : ((stripos($lower, 'slide') !== false) ? 'slide' : 'banner');
    $stmt = $pdo->prepare('INSERT INTO announcements (content, kind, active, priority) VALUES (?,?,1,10)');
    $stmt->execute([$cmd, $kind]);
    echo '<div class="text-green-700">Announcement created (' . htmlspecialchars($kind) . ').</div>';
    exit;
  }

  if (strpos($lower, 'deactivate announcements') !== false || strpos($lower, 'clear announcements') !== false) {
    $pdo->exec('UPDATE announcements SET active=0 WHERE active=1');
    echo '<div class="text-amber-700">All announcements deactivated.</div>';
    exit;
  }

  if (preg_match('/set\s+color\s+primary\s+([#a-z0-9(),.%\-\s]+)/i', $cmd, $m)) {
    $val = trim($m[1]);
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('primary_color', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$val]);
    echo '<div class="text-green-700">Primary color updated.</div>';
    exit;
  }

  // Set background image by URL with optional opacity: set background image <url> opacity 0.3
  if (preg_match('/set\s+background\s+image\s+(\S+)(?:\s+opacity\s+([01](?:\.\d+)?))?/i', $cmd, $m)) {
    $url = trim($m[1]);
    $opacity = isset($m[2]) ? trim($m[2]) : '0.2';
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('bg_image_url', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$url]);
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('bg_image_opacity', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$opacity]);
    echo '<div class="text-green-700">Background image set with opacity ' . htmlspecialchars($opacity) . '.</div>';
    exit;
  }

  // Set background by keyword (auto image) with optional opacity: set background pizza opacity 0.25
  if (preg_match('/set\s+background\s+([a-zA-Z]+)/i', $cmd, $m)) {
    $kw = strtolower(trim($m[1]));
    $map = [
      'pizza' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=1600&q=80&auto=format&fit=crop',
      'burger' => 'https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=1600&q=80&auto=format&fit=crop',
      'sushi' => 'https://images.unsplash.com/photo-1563379091339-03246963d4d0?w=1600&q=80&auto=format&fit=crop',
      'coffee' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=1600&q=80&auto=format&fit=crop',
      'dessert' => 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?w=1600&q=80&auto=format&fit=crop',
    ];
    $url = $map[$kw] ?? 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?w=1600&q=80&auto=format&fit=crop';
    $opacity = '0.2';
    if (preg_match('/opacity\s+([01](?:\.\d+)?)/i', $cmd, $mm)) { $opacity = trim($mm[1]); }
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('bg_image_url', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$url]);
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('bg_image_opacity', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$opacity]);
    echo '<div class="text-green-700">Background set to ' . htmlspecialchars($kw) . ' with opacity ' . htmlspecialchars($opacity) . '.</div>';
    exit;
  }

  // Clear background image
  if (preg_match('/clear\s+background/i', $cmd)) {
    $pdo->exec("DELETE FROM site_config WHERE cfg_key IN ('bg_image_url','bg_image_opacity')");
    echo '<div class="text-amber-700">Background image cleared.</div>';
    exit;
  }

  if (preg_match('/set\s+font\s+([A-Za-z0-9\-\s,]+)$/i', $cmd, $m)) {
    $val = trim($m[1]);
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('font_family', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$val]);
    echo '<div class="text-green-700">Font updated.</div>';
    exit;
  }

  if (preg_match('/(enable|disable)\s+(extra\s+panel|panel)/i', $cmd, $m)) {
    $on = strtolower($m[1]) === 'enable' ? '1' : '0';
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('show_extra_panel', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$on]);
    echo $on==='1' ? '<div class="text-green-700">Extra panel enabled.</div>' : '<div class="text-amber-700">Extra panel disabled.</div>';
    exit;
  }

  if (preg_match('/set\s+layout\s+(compact|spacious)/i', $cmd, $m)) {
    $val = strtolower($m[1]);
    $stmt = $pdo->prepare("INSERT INTO site_config (cfg_key, cfg_value) VALUES ('layout_variant', ?) ON DUPLICATE KEY UPDATE cfg_value=VALUES(cfg_value)");
    $stmt->execute([$val]);
    echo '<div class="text-green-700">Layout updated.</div>';
    exit;
  }

  if (preg_match("/^(create|add)\s+ad\s+'?([\w\s-]+)'?/i", $cmd, $m)) {
    $title = trim($m[2]);
    ensureAdsSchemaLocal($pdo);
    $userId = resolveCurrentUserId($pdo);
    $stmt = $pdo->prepare("INSERT INTO ads (user_id, title, status) VALUES (?,?, 'draft')");
    $stmt->execute([$userId, $title]);
    echo '<div class="text-green-700">Ad created: ' . htmlspecialchars($title) . ' (draft)</div>';
    exit;
  }

  if (preg_match("/^approve\s+user\s+#?(\d+)/i", $cmd, $m)) {
    $uid = (int)$m[1];
    $pdo->prepare("UPDATE users SET is_approved=1 WHERE id=?")->execute([$uid]);
    echo '<div class="text-green-700">User #' . $uid . ' approved.</div>';
    exit;
  }

  if (preg_match("/^enable\s+feature\s+([\w_-]+)/i", $cmd, $m)) {
    $feature = strtolower($m[1]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS features (name VARCHAR(64) PRIMARY KEY, enabled TINYINT(1) NOT NULL DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
    $stmt = $pdo->prepare("INSERT INTO features (name, enabled) VALUES (?,1) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)");
    $stmt->execute([$feature]);
    echo '<div class="text-green-700">Feature enabled: ' . htmlspecialchars($feature) . '</div>';
    exit;
  }

  if (preg_match("/^disable\s+feature\s+([\w_-]+)/i", $cmd, $m)) {
    $feature = strtolower($m[1]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS features (name VARCHAR(64) PRIMARY KEY, enabled TINYINT(1) NOT NULL DEFAULT 0, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)");
    $stmt = $pdo->prepare("INSERT INTO features (name, enabled) VALUES (?,0) ON DUPLICATE KEY UPDATE enabled=VALUES(enabled)");
    $stmt->execute([$feature]);
    echo '<div class="text-amber-700">Feature disabled: ' . htmlspecialchars($feature) . '</div>';
    exit;
  }

  // Fallback: if command includes 'campaign', create a campaign; else create an ad
  if (preg_match("/campaign/i", $lower)) {
    $title = trim(preg_replace("/campaign/i", '', $cmd));
    if ($title === '') { $title = 'Untitled Campaign'; }
    ensureSchema($pdo);
    $stmt = $pdo->prepare("INSERT INTO campaigns (title, message, image_url, is_active) VALUES (?,?,?,1)");
    $stmt->execute([$title, null, null]);
    $id = (int)$pdo->lastInsertId();
    echo '<div class="text-green-700">Campaign created #' . $id . ': ' . htmlspecialchars($title) . '</div>';
    echo '<div class="mt-2">'
       . '<button class="px-2 py-1 text-sm bg-rose-600 text-white rounded" '
       . 'hx-post="portal_ai_console_action.php" '
       . 'hx-vals=' . json_encode(['command' => 'delete campaign #' . $id]) . ' '
       . 'hx-target="#ai-result" hx-swap="innerHTML">Delete</button>'
       . '</div>';
    exit;
  } else {
    $title = trim($cmd);
    ensureAdsSchemaLocal($pdo);
    $userId = resolveCurrentUserId($pdo);
    $stmt = $pdo->prepare("INSERT INTO ads (user_id, title, status) VALUES (?,?, 'draft')");
    $stmt->execute([$userId, $title]);
    echo '<div class="text-green-700">Ad created: ' . htmlspecialchars($title) . ' (draft)</div>';
    exit;
  }
} catch (Throwable $e) {
  echo '<div class="text-rose-700">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}



