<?php
// Simple placeholder that captures email and redirects back to home with a success query
session_start();
$email = trim($_POST['email'] ?? '');
if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
  // Persist emails to a flat file for now; you can replace with DB or provider API later
  $file = __DIR__ . '/newsletter_emails.txt';
  file_put_contents($file, $email . "\n", FILE_APPEND | LOCK_EX);
  header('Location: home.php?subscribed=1');
  exit;
}
header('Location: home.php');
exit;




