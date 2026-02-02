<?php
session_start();
require_once 'db.php';
requireLogin();
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare('DELETE FROM expenses WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
}
header('Location: expenses.php');
exit;
?>
