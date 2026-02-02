<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Fetch user info for sidebar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title = trim($_POST['title']);
    $amount = (float) $_POST['amount'];
    $category = trim($_POST['category']);
    $date = $_POST['date'];
    $notes = trim($_POST['notes']);

    $stmt = $pdo->prepare('INSERT INTO expenses (user_id,title,amount,category,date,notes) VALUES (?,?,?,?,?,?)');
    $stmt->execute([$userId,$title,$amount,$category,$date,$notes]);

    header('Location: expenses.php');
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Add Expense - Expense Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/card.css">
<link rel="stylesheet" href="assets/css/nav.css">
<link rel="stylesheet" href="assets/css/dark-mode.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include 'nav.php'; ?>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <div class="card">
        <h2>Add Expense</h2>
        <form method="post">
            <input type="text" name="title" placeholder="Title" required>
            <input type="number" step="0.01" name="amount" placeholder="Amount" required>
            <input type="text" name="category" placeholder="Category" required>
            <input type="date" name="date" required>
            <textarea name="notes" placeholder="Notes"></textarea>
            <div class="btn-row">
                <button type="submit" class="add">Add Expense</button>
            </div>
        </form>
    </div>
</div>

<script>
const sidebar = document.getElementById("sidebar");
const menuButton = document.getElementById("menuButton");
const drawerOverlay = document.getElementById("drawerOverlay");
const sidebarLinks = sidebar.querySelectorAll("a");

function openDrawer(){
    sidebar.classList.add("open");
    drawerOverlay.classList.add("visible");
}
function closeDrawer(){
    sidebar.classList.remove("open");
    drawerOverlay.classList.remove("visible");
}

if(menuButton){
    menuButton.addEventListener("click", function(){
        if(sidebar.classList.contains("open")){
            closeDrawer();
        } else {
            openDrawer();
        }
    });
}

drawerOverlay.addEventListener("click", closeDrawer);

sidebarLinks.forEach(function(link){
    link.addEventListener("click", function(){
        if(window.innerWidth <= 768){
            closeDrawer();
        }
    });
});
</script>

</body>
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
</html>
