<?php
session_start();
require_once "db.php"; // make sure db.php connects to your database

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $message = '<p class="error">All fields are required.</p>';
    } else {
        try {
            $pdo = getDBConnection();

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $message = '<p class="error">Email already registered. 
                            <a href="login.php">Login here</a></p>';
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert new user
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword]);

                $message = '<p class="success">Registration successful! 
                            <a href="login.php">Click here to Login</a></p>';
            }
        } catch (PDOException $e) {
            $message = '<p class="error">Error: ' . $e->getMessage() . '</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | FoodieAds</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#f97316">
    <link rel="apple-touch-icon" href="/icons/spoon.svg">
    <link rel="icon" href="/icons/spoon.svg" type="image/svg+xml">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/theme.js?v=20250915" defer></script>
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js').catch(err => console.error('SW register failed', err));
            }
        </script>
</head>
<body class="bg-slate-50">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="min-h-screen grid place-items-center px-6">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">Create your account</h1>
                <p class="text-slate-600 text-sm">Start your free trial today</p>
            </div>
            <div class="fa-card p-6">
                <?php if ($message): ?>
                    <div class="mb-3 text-sm"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1">Username</label>
                        <input class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" type="text" name="username" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" type="email" name="email" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Password</label>
                        <div class="relative">
                            <input id="signup-password" class="w-full border border-slate-200 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500" type="password" name="password" required>
                            <button type="button" aria-label="Show password" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700 text-sm" onclick="(function(btn){var i=document.getElementById('signup-password'); if(!i)return; var isPw=i.type==='password'; i.type=isPw?'text':'password'; btn.textContent=isPw?'Hide':'Show';})(this)">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="w-full fa-btn">Sign Up</button>
                </form>
                <div class="text-center mt-4 text-sm">
                    <a class="text-orange-600 hover:underline" href="login.php">Already have an account? Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
