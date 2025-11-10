<?php
require_once __DIR__ . '/db.php';

function getPdo(): PDO {
    return getDBConnection();
}

function ensureSchema(PDO $pdo): void {
    // Subscriptions table
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS subscriptions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                plan VARCHAR(32) NOT NULL,
                amount INT NOT NULL,
                billing_period ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly',
                payment_method VARCHAR(32) NOT NULL,
                mpesa_code VARCHAR(64) NULL,
                status ENUM('pending','active','failed','cancelled') NOT NULL DEFAULT 'pending',
                start_date DATETIME NULL,
                end_date DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

    // Campaigns table for homepage floating announcements
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS campaigns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            message TEXT NULL,
            image_url VARCHAR(1024) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
    // Users optional columns: created_at, is_approved, role, admin_pass_hash
    try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS is_approved TINYINT(1) NOT NULL DEFAULT 0"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(32) NOT NULL DEFAULT 'user'"); } catch (Throwable $e) {}
    try { $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS admin_pass_hash VARCHAR(255) NULL"); } catch (Throwable $e) {}
}

function planToAmountAndTrial(string $plan): array {
    $map = [
        'basic' => ['amount' => 500, 'trial_days' => 7],
        'standard' => ['amount' => 1000, 'trial_days' => 14],
        'premium' => ['amount' => 1500, 'trial_days' => 30],
    ];
    return $map[$plan] ?? $map['basic']; // Default to basic plan
    if ($period === 'yearly') {
        $base['amount'] = (int)($base['amount'] * 12 * 0.8); // 20% discount
    }
    return $base;
}

function createPendingSubscription(PDO $pdo, int $userId, string $plan, string $paymentMethod, ?string $mpesaCode = null): int {
    ensureSchema($pdo);
    $info = planToAmountAndTrial($plan, $billingPeriod);
    $stmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan, amount, billing_period, payment_method, mpesa_code, status) VALUES (?,?,?,?,?,?, 'pending')");
    $stmt->execute([$userId, $plan, $info['amount'], $billingPeriod, $paymentMethod, $mpesaCode]);
    return (int)$pdo->lastInsertId();
}

function activateSubscription(PDO $pdo, int $subscriptionId): void {
    $stmt = $pdo->prepare("SELECT plan, user_id FROM subscriptions WHERE id=?");
    $stmt->execute([$subscriptionId]);
    $sub = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sub) return;
    $trial = planToAmountAndTrial($sub['plan']);
    $start = (new DateTime())->format('Y-m-d H:i:s');
    $end = (new DateTime('+30 days'))->format('Y-m-d H:i:s');
    $upd = $pdo->prepare("UPDATE subscriptions SET status='active', start_date=?, end_date=? WHERE id=?");
    $upd->execute([$start, $end, $subscriptionId]);
    // Mark user approved upon activation
    try {
        $pdo->prepare("UPDATE users SET is_approved=1 WHERE id=?")->execute([$sub['user_id']]);
    } catch (Throwable $e) {}
}

function failSubscription(PDO $pdo, int $subscriptionId): void {
    $pdo->prepare("UPDATE subscriptions SET status='failed' WHERE id=?")->execute([$subscriptionId]);
}

function getActiveSubscription(PDO $pdo, int $userId): ?array {
    ensureSchema($pdo);
    $stmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id=? AND status='active' AND (end_date IS NULL OR end_date >= NOW()) ORDER BY end_date DESC LIMIT 1");
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function listPendingSubscriptions(PDO $pdo): array {
    $stmt = $pdo->query("SELECT s.*, u.name, u.email FROM subscriptions s JOIN users u ON u.id = s.user_id WHERE s.status='pending' ORDER BY s.created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listRevenueByMonth(PDO $pdo): array {
    $sql = "SELECT DATE_FORMAT(COALESCE(start_date, created_at),'%Y-%m') AS ym, SUM(amount) AS revenue
            FROM subscriptions WHERE status='active' GROUP BY ym ORDER BY ym";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function listRegistrationsByMonth(PDO $pdo): array {
    try {
        $stmt = $pdo->query("SELECT DATE_FORMAT(COALESCE(created_at, NOW()), '%Y-%m') AS ym, COUNT(*) AS total FROM users GROUP BY ym ORDER BY ym");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

function ensureSuperAdmin(PDO $pdo, string $email, string $plainAdminPassword): void {
    ensureSchema($pdo);
    $hash = password_hash($plainAdminPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    try {
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $upd = $pdo->prepare("UPDATE users SET role='super_admin', admin_pass_hash=? WHERE id=?");
            $upd->execute([$hash, $user['id']]);
        } else {
            // Create minimal user if not exists
            $ins = $pdo->prepare("INSERT INTO users (name, email, password, role, is_approved, admin_pass_hash) VALUES (?,?,?,?,1,?)");
            $ins->execute(['Super Admin', $email, $hash, 'super_admin', $hash]);
        }
    } catch (Throwable $e) {
        // swallow
    }
}



