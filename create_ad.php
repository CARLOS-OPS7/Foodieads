<?php
session_start();
require_once "db.php"; // make sure this connects to your DB

session_start();
require_once "db.php"; // Make sure this path points to your db.php file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Initialize PDO connection
$pdo = getDBConnection(); // This function should be defined in db.php

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$stmt = $pdo->prepare("...");

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $user_id = $_SESSION['user_id'];

    // Handle image upload
    $imagePath = "";
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imagePath = $targetDir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
    }

    // Insert ad
    $stmt = $pdo->prepare("INSERT INTO ads (user_id, title, description, category, image) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $title, $description, $category, $imagePath])) {
        $message = "✅ Ad created successfully!";
    } else {
        $message = "❌ Failed to create ad.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Ad - FoodieAds</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #f97316, #16a34a);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .container {
      background: #fff;
      padding: 2rem;
      border-radius: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.15);
      max-width: 500px;
      width: 100%;
      animation: fadeInUp 0.8s ease;
    }
    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      font-weight: 600;
      display: block;
      margin-bottom: 0.5rem;
      color: #555;
    }
    input, textarea, select {
      width: 100%;
      padding: 0.75rem;
      border: 2px solid #e5e7eb;
      border-radius: 12px;
      font-size: 1rem;
      transition: border 0.3s;
    }
    input:focus, textarea:focus, select:focus {
      border-color: #16a34a;
      outline: none;
    }
    button {
      width: 100%;
      padding: 0.9rem;
      background: linear-gradient(135deg, #f97316, #16a34a);
      color: #fff;
      font-weight: bold;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 1rem;
    }
    button:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }
    .message {
      text-align: center;
      margin-bottom: 1rem;
      font-weight: bold;
      color: #16a34a;
    }
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/partials/navbar.php'; ?>
  <div class="container">
    <h2>Create Your Ad</h2>

    <?php if (!empty($message)): ?>
      <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="title">Ad Title</label>
        <input type="text" id="title" name="title" required>
      </div>

      <div class="form-group">
        <label for="description">Ad Description</label>
        <textarea id="description" name="description" rows="4" required></textarea>
      </div>

      <div class="form-group">
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="Food Delivery">Food Delivery</option>
          <option value="Dine-In">Dine-In</option>
          <option value="Takeaway">Takeaway</option>
          <option value="Special Offers">Special Offers</option>
        </select>
      </div>

      <div class="form-group">
        <label for="image">Ad Image</label>
        <input type="file" id="image" name="image" accept="image/*">
      </div>

      <button type="submit">Create Ad</button>
    </form>
  </div>
</body>
</html>
