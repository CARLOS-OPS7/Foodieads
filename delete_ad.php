<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$ad_id = $_GET['id'] ?? 0;

// Fetch ad details
$stmt = getDBConnection()->prepare("SELECT image FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$ad_id, $_SESSION['user_id']]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    die("Ad not found.");
}

// Delete image
if($ad['image'] && file_exists("uploads/".$ad['image'])) {
    unlink("uploads/".$ad['image']);
}

// Delete ad record
$stmt = getDBConnection()->prepare("DELETE FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$ad_id, $_SESSION['user_id']]);

header("Location: dashboard.php?msg=Ad+deleted+successfully");
exit;
?>
