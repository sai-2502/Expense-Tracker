<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Fetch user info for sidebar
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);


// Fetch categories for filter dropdown
$catStmt = $pdo->prepare('SELECT DISTINCT category FROM expenses WHERE user_id = ? ORDER BY category');
$catStmt->execute([$userId]);
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Filter logic
$where = 'user_id = ?';
$params = [$userId];
if(!empty($_GET['q'])){
    $where .= ' AND (title LIKE ? OR category LIKE ?)';
    $params[] = '%'.$_GET['q'].'%';
    $params[] = '%'.$_GET['q'].'%';
}
if(!empty($_GET['from']) && !empty($_GET['to'])){
    $where .= ' AND date BETWEEN ? AND ?';
    $params[] = $_GET['from'];
    $params[] = $_GET['to'];
}
if(!empty($_GET['month'])){
    $where .= ' AND MONTH(date) = ?';
    $params[] = (int)$_GET['month'];
}
if(!empty($_GET['category'])){
    $where .= ' AND category = ?';
    $params[] = $_GET['category'];
}
$sort = 'date DESC';
if(!empty($_GET['sort'])){
    if($_GET['sort'] === 'amount_asc') $sort = 'amount ASC';
    if($_GET['sort'] === 'amount_desc') $sort = 'amount DESC';
    if($_GET['sort'] === 'date_asc') $sort = 'date ASC';
    if($_GET['sort'] === 'date_desc') $sort = 'date DESC';
}

$stmt = $pdo->prepare('SELECT * FROM expenses WHERE '.$where.' ORDER BY '.$sort);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
<!-- Font Awesome for icons -->

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>All Expenses Expense Tracker</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/card.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="assets/css/nav.css">
<link rel="stylesheet" href="assets/css/dark-mode.css">
</head>
<body>

    <?php include 'nav.php'; ?>
    <!-- Main Wrapper -->
    <section class="main-container">
    <div class="main-wrapper" style="padding-top: 32px; flex: 1 1 0;">
        <div class="card full-width">
        <h2>All Expenses</h2>
        <form method="get" class="filter">
            <input name="q" placeholder="Search title or category" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <select name="month">
                <option value="">All Months</option>
                <?php for($m=1;$m<=12;$m++): ?>
                    <option value="<?= $m ?>" <?= (isset($_GET['month']) && $_GET['month']==$m)?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_GET['category']) && $_GET['category']==$cat)?'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
            <input name="from" type="date" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
            <input name="to" type="date" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
            <select name="sort">
                <option value="date_desc" <?= (!isset($_GET['sort'])||$_GET['sort']==='date_desc')?'selected':'' ?>>Newest First</option>
                <option value="date_asc" <?= (isset($_GET['sort'])&&$_GET['sort']==='date_asc')?'selected':'' ?>>Oldest First</option>
                <option value="amount_desc" <?= (isset($_GET['sort'])&&$_GET['sort']==='amount_desc')?'selected':'' ?>>Amount High-Low</option>
                <option value="amount_asc" <?= (isset($_GET['sort'])&&$_GET['sort']==='amount_asc')?'selected':'' ?>>Amount Low-High</option>
            </select>
            <button type="submit">Filter</button>
            <a href="expenses.php" class="btn" style="margin-left:8px;">Reset</a>
                    <!-- Add more filter options here if needed -->
        </form>

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($items as $it): ?>
                    <tr>
                        <td data-label="Date"><?= htmlspecialchars($it['date']) ?></td>
                        <td data-label="Title"><?= htmlspecialchars($it['title']) ?></td>
                        <td data-label="Category"><?= htmlspecialchars($it['category']) ?></td>
                        <td data-label="Amount"><?= number_format($it['amount'],2) ?></td>
                        <td data-label="Actions" style="white-space:nowrap;">
                            <a href="edit_expense.php?id=<?= $it['id'] ?>" class="action-icon edit" title="Edit">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form method="post" action="delete_expense.php" style="display:inline">
                                <input type="hidden" name="id" value="<?= $it['id'] ?>">
                                <button type="submit" class="danger action-icon delete" title="Delete expense">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                           
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
    </div>
     </section>
</div>


<script>
// Sidebar drawer logic (shared)
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
