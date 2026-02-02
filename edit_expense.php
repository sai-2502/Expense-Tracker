<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

if(!isset($_GET['id'])){
    header('Location: expenses.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $userId]);
$expense = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$expense){
    header('Location: expenses.php');
    exit;
}

// Fetch user info for sidebar
$stmt = $pdo->prepare("SELECT id, name, profile_pic FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update'){
    $title    = trim($_POST['title']);
    $amount   = (float) $_POST['amount'];
    $category = trim($_POST['category']);
    $date     = $_POST['date'];
    $notes    = trim($_POST['notes']);

    $stmt = $pdo->prepare('UPDATE expenses SET title=?, amount=?, category=?, date=?, notes=? WHERE id=? AND user_id=?');
    $stmt->execute([$title, $amount, $category, $date, $notes, $id, $userId]);
    header('Location: expenses.php');
    exit;
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Expense</title>
<link rel="stylesheet" href="assets/css/nav.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/card.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/dark-mode.css"> 
</head>
<body>

<div class="page-flex" style="display: flex; min-height: 100vh;">
    <?php include 'nav.php'; ?>
    <div class="main-wrapper" style="flex: 1 1 0; display: flex; align-items: flex-start; justify-content: center; padding-top: 48px;">
        <div class="card" style="width:100%; max-width:480px;">
            <h2>Edit Expense</h2>
            <!-- Update form -->
            <form method="post">
                <input type="hidden" name="action" value="update">
                <input name="title" value="<?= htmlspecialchars($expense['title']) ?>" placeholder="Title" required>
                <input name="amount" type="number" step="0.01" value="<?= htmlspecialchars($expense['amount']) ?>" placeholder="Amount" required>
                <input name="category" value="<?= htmlspecialchars($expense['category']) ?>" placeholder="Category" required>
                <input name="date" type="date" value="<?= htmlspecialchars($expense['date']) ?>" required>
                <textarea name="notes" placeholder="Notes"><?= htmlspecialchars($expense['notes']) ?></textarea>
                <div class="btn-row">
                    <button type="submit" class="update">Update</button>
                </div>
            </form>
            <!-- Delete form (separate, not nested) -->
            <form method="post" action="delete_expense.php" onsubmit="return confirm('Are you sure you want to delete this expense?');" style="margin-top:10px;">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button type="submit" class="danger action-icon delete" style="width:100%" title="Delete expense">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>


<script>
// Theme toggle with icon (shared for all pages)
const themeBox = document.getElementById("themeToggleBox");
if(themeBox){
    const updateThemeIcon = () => {
        if(document.body.classList.contains("dark")){
            themeBox.innerHTML = '<span style="font-size:1.1em;"><i class="fa-solid fa-sun"></i></span> <span style="font-size:14px;">Theme</span>';
        } else {
            themeBox.innerHTML = '<span style="font-size:1.1em;"><i class="fa-solid fa-moon"></i></span> <span style="font-size:14px;">Theme</span>';
        }
    };
    if(localStorage.getItem("theme") === "dark"){
        document.body.classList.add("dark");
    }
    updateThemeIcon();
    themeBox.onclick = () => {
        document.body.classList.toggle("dark");
        if(document.body.classList.contains("dark")){
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
        updateThemeIcon();
    };
}
</script>
</body>
</html>
