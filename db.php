<?php
// db.php
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'auth_system'; // Make sure this DB exists in phpMyAdmin
    $user = 'root';
    $pass = ''; // Your MySQL password, usually empty for XAMPP

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("DB Connection failed: " . $e->getMessage());
    }
}
