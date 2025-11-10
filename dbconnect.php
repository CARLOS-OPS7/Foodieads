<?php
require_once 'dbconnect.php';

session_start();
$errors = [];
$success = false;


session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome to your Dashboard, <?php echo $_SESSION['user']; ?> 🎉</h1>
    <p>This is your personalized dashboard styled like FoodieAds.</p>
    <a href="logout.php">Logout</a>
</body>
</html>

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    // Sanitize and validate input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $created_at = filter_input(INPUT_POST, 'created_at', FILTER_SANITIZE_STRING);   

    // Validation
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Email already registered";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error occurred";
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    // Check if username already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username already taken";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error occurred";
            error_log("Database error: " . $e->getMessage());
        }
    }
    
    // Insert new user if no errors
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = true;
                $_SESSION['signup_success'] = "Account created successfully! You can now log in.";
                
                // Optionally redirect to login page
                // header("Location: login.php");
                // exit();
            } else {
                $errors[] = "Failed to create account";
            }
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
            error_log("Database error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        
        button:hover {
            background-color: #0056b3;
        }
        
        .error {
            color: red;
            margin-bottom: 10px;
        }
        
        .success {
            color: green;
            margin-bottom: 10px;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Sign Up</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">
            Account created successfully! You can now <a href="login.php">log in</a>.
        </div>
    <?php endif; ?>
    
    <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" name="signup">Sign Up</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Log in here</a>
        </div>
    <?php endif; ?>
</body>
</html>