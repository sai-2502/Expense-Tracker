<?php
// nav.php - shared navigation/sidebar for all pages
if (!isset($user)) {
    // If $user is not set, try to fetch from session
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/db.php';
    requireLogin();
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<link rel="stylesheet" href="assets/css/nav.css">
<div class="mobile-header">
    <button class="menu-button" id="menuButton">☰</button>
    <span class="mobile-header-title">Expense Tracker</span>
</div>
<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="sidebar" id="sidebar">
    <h2>Expense Tracker</h2>
    <div class="nav-links">
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="add_expense.php">➕ Add Expense</a>
        <a href="expenses.php">💰 Expenses</a>
        <a href="profile.php">👤 Profile</a>
        <a href="help.php">🆘 Help & Support</a>
        <a href="logout.php">🚪 Logout</a>
    </div>
    <div class="profile-box">
        <img src="<?= $user['profile_pic'] ? 'uploads/profile_pics/' . htmlspecialchars($user['profile_pic']) : 'assets/img/default.png' ?>" alt="User">
        <p><?= htmlspecialchars($user['name']) ?></p>
        <div id="themeToggleBox" style="margin: 14px 0 0 0; cursor:pointer; font-size:1.3em; display:flex; align-items:center; gap:8px; justify-content:center;">
          <span style="font-size:14px;">Theme</span>   
        <span style="font-size:1.1em;"><i class="fa-solid fa-moon"></i></span>
          
        </div>
    </div>
</div>
<script>
// Sidebar drawer logic
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

// Theme toggle logic (always present)
document.addEventListener('DOMContentLoaded', function() {
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
});
</script>
