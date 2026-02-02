<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Fetch current password hash
    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current, $user['password'])) {
        header('Location: profile.php?password=fail');
        exit;
    }
    if (strlen($new) < 6) {
        header('Location: profile.php?password=fail');
        exit;
    }
    if ($new !== $confirm) {
        header('Location: profile.php?password=fail');
        exit;
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
    $stmt->execute([$newHash, $userId]);
    header('Location: profile.php?password=success');
    exit;
}
// If not POST, redirect to profile
header('Location: profile.php');
exit;
