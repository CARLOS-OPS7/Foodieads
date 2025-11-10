<?php
session_start();
require_once "db.php";

$pdo = getDBConnection();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["user_name"] = $user["name"] ?? $user["username"] ?? '';
            header("Location: dashboard.php"); // redirect after login
            exit;
        } else {
            $error = "Invalid email or password!";
        }
    } else {
        $error = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodieAds</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/styles.css">
    <script src="assets/theme.js?v=20250915" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>body{font-family:'Poppins',sans-serif}</style>
</head>
<body class="bg-slate-50">
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="min-h-screen grid place-items-center px-6">
        <div class="w-full max-w-md">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold">Welcome back</h1>
                <p class="text-slate-600 text-sm">Log in to manage your campaigns</p>
            </div>
            <div class="fa-card p-6">
                <?php if (!empty($error)) echo "<p class='text-red-600 text-sm mb-3'>".htmlspecialchars($error)."</p>"; ?>
                <form method="POST" class="space-y-3">
                    <div>
                        <input class="w-full border border-slate-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" type="email" name="email" placeholder="Email" required>
                    </div>
                    <div>
                        <div class="relative">
                            <input id="login-password" class="w-full border border-slate-200 rounded-lg px-3 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500" type="password" name="password" placeholder="Password" required>
                            <button type="button" aria-label="Show password" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700 text-sm" onclick="(function(btn){var i=document.getElementById('login-password'); if(!i)return; var isPw=i.type==='password'; i.type=isPw?'text':'password'; btn.textContent=isPw?'Hide':'Show';})(this)">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="w-full fa-btn text-center">Login</button>
                </form>
                <div class="text-center mt-4 text-sm">
                    <a class="text-orange-600 hover:underline" href="index.php">Don’t have an account? Sign up</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
 