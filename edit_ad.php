<?php
session_start();
require_once "db.php";

// Redirect if user not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get ad ID from query string
$adId = $_GET['id'] ?? null;
if (!$adId) {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$messageType = '';

try {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
    $stmt->execute([$adId, $_SESSION['user_id']]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        $message = "Ad not found or you don't have permission to edit it.";
        $messageType = 'error';
    }
} catch (PDOException $e) {
    $message = "Database error: " . $e->getMessage();
    $messageType = 'error';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $imageName = $ad['image']; // keep old image if not changed

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $fileTmp = $_FILES['image']['tmp_name'];
        $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (in_array($fileExt, $allowed)) {
            $newImageName = uniqid('ad_', true) . '.' . $fileExt;
            move_uploaded_file($fileTmp, $uploadDir . $newImageName);
            
            // delete old image
            if ($ad['image'] && file_exists($uploadDir . $ad['image'])) {
                unlink($uploadDir . $ad['image']);
            }

            $imageName = $newImageName;
        } else {
            $message = "Invalid image type. Allowed: jpg, jpeg, png, gif.";
            $messageType = 'error';
        }
    }

    if (!$message) {
        try {
            $stmt = $pdo->prepare("UPDATE ads SET title = ?, description = ?, image = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $description, $imageName, $adId, $_SESSION['user_id']]);
            $message = "Ad updated successfully!";
            $messageType = 'success';

            // Refresh ad data
            $stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
            $stmt->execute([$adId, $_SESSION['user_id']]);
            $ad = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Ad - FoodieAds</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="assets/styles.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea, #764ba2);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 50px;
}
.container {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 500px;
}
h1 { text-align: center; color: #333; margin-bottom: 1.5rem; }
.form-group { margin-bottom: 1rem; }
label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #555; }
input[type="text"], textarea, input[type="file"] {
    width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9;
    border-radius: 5px; font-size: 1rem; transition: border-color 0.3s;
}
input[type="text"]:focus, textarea:focus { border-color: #667eea; outline: none; }
textarea { resize: vertical; min-height: 100px; }
.btn {
    width: 100%; padding: 0.75rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white; border: none; border-radius: 5px;
    font-size: 1rem; cursor: pointer; transition: transform 0.2s;
}
.btn:hover { transform: translateY(-2px); }
.message { padding: 0.75rem; margin-bottom: 1rem; border-radius: 5px; text-align: center; }
.message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
img.preview { display: block; max-width: 100%; margin-top: 1rem; border-radius: 5px; }
</style>
</head>
<body>
<?php include __DIR__ . '/partials/navbar.php'; ?>
<div class="container">
    <h1>Edit Ad</h1>

    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <?php if ($ad): ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Ad Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($ad['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*">
            <?php if ($ad['image']): ?>
                <img src="uploads/<?php echo htmlspecialchars($ad['image']); ?>" class="preview" alt="Current Image">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn">Update Ad</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
