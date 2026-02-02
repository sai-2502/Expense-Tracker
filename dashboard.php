
<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Fetch user

// Fetch user including budget_warn_limit
$stmt = $pdo->prepare("SELECT id, name, email, profile_pic, monthly_budget, budget_warn_limit FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Monthly budget from database or default
$monthly_budget = isset($user['monthly_budget']) ? (float)$user['monthly_budget'] : 0.00;

// Set budget warning limit from DB or default 90%
$budget_warn_limit = isset($user['budget_warn_limit']) ? (int)$user['budget_warn_limit'] : 90;
if($budget_warn_limit < 1 || $budget_warn_limit > 100) $budget_warn_limit = 90;

// Update budget if form submitted
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['monthly_budget'])){
        $monthly_budget = (float) $_POST['monthly_budget'];
        $stmt = $pdo->prepare("UPDATE users SET monthly_budget = ? WHERE id = ?");
        $stmt->execute([$monthly_budget, $userId]);
        header('Location: dashboard.php');
        exit;
    }
    if(isset($_POST['budget_warn_limit'])){
        $budget_warn_limit = max(1, min(100, (int)$_POST['budget_warn_limit']));
        // Update in database
        $stmt = $pdo->prepare("UPDATE users SET budget_warn_limit = ? WHERE id = ?");
        $stmt->execute([$budget_warn_limit, $userId]);
        header('Location: dashboard.php');
        exit;
    }
}

// Totals by category
$stmt = $pdo->prepare("SELECT category, SUM(amount) as total FROM expenses WHERE user_id = ? GROUP BY category");
$stmt->execute([$userId]);
$categoryTotals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total expenses
$totalExpenses = array_sum(array_column($categoryTotals, 'total'));

// Recent expenses
$stmt = $pdo->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY date DESC LIMIT 50");
$stmt->execute([$userId]);
$recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Expense Tracker</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation@1.3.1/dist/chartjs-plugin-annotation.min.js"></script>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="assets/css/nav.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<div class="budget-popup-overlay" id="budgetPopupOverlay">
    <div class="budget-popup">
        <h3>Over budget alert</h3>
        <p>You have spent more than your monthly budget. Please review your expenses.</p>
        <button id="closeBudgetPopup">Got it</button>
    </div>
</div>


<div class="mobile-header">
    <button class="menu-button" id="menuButton">☰</button>
    <span class="mobile-header-title">Expense Tracker</span>
</div>
<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="sidebar" id="sidebar">
    <h2>Expense Tracker</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="add_expense.php">➕ Add Expense</a>
    <a href="expenses.php">💰 Expenses</a>
    <a href="profile.php">👤 Profile</a>
    <a href="help.php">🆘 Help & Support</a>
    <a href="logout.php">🚪 Logout</a>
    <div class="profile-box">
        <img src="<?= $user['profile_pic'] ? 'uploads/profile_pics/' . htmlspecialchars($user['profile_pic']) : 'assets/img/default.png' ?>" alt="User">
        <p><?= htmlspecialchars($user['name']) ?></p>
       
    </div>
</div>


<!-- Main -->
<div class="main">

    <!-- Welcome Section -->
    <section class="card">
        <div style="display:flex; justify-content: space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:15px;">
            <h2 style="margin:0;">Welcome <?= htmlspecialchars($user['name']) ?></h2>

            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <a href="add_expense.php" class="add-btn">+ Add Expense</a>

                <form method="post" action="download_expenses.php" style="display:inline-block; margin:0;">
                    <button type="submit" class="add-btn" style="background:#10b981;">⬇ Download CSV</button>
                </form>

                <form method="post" style="display:inline-flex; gap:6px; align-items:center; margin:0;">
                    <input
                        type="number"
                        step="1"
                        min="0"
                        name="monthly_budget"
                        value="<?= htmlspecialchars((int)$monthly_budget) ?>"
                        style="width:120px; padding:6px 8px; border-radius:6px; border:1px solid #d1d5db; font-size:14px;"
                    >
                    <button type="submit" style="padding:6px 10px; font-size:14px;">Save Budget</button>
                </form>

                <form method="post" style="display:inline-flex; gap:6px; align-items:center; margin:0;">
                    <label for="budget_warn_limit" style="font-size:13px; color:#6b7280;">Warn at</label>
                    <input
                        type="number"
                        min="1"
                        max="100"
                        name="budget_warn_limit"
                        id="budget_warn_limit"
                        value="<?= htmlspecialchars($budget_warn_limit) ?>"
                        style="width:48px; padding:4px 6px; border-radius:6px; border:1px solid #d1d5db; font-size:13px;"
                    >
                    <span style="font-size:13px; color:#6b7280;">%</span>
                    <button type="submit" style="padding:4px 8px; font-size:13px;">Set</button>
                </form>

                <div id="themeToggleBox"><i class="fa-solid fa-moon"></i></div>
            </div>
        </div>

        <div class="grid-two">
            <div style="min-height:260px;">
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:10px;">
                    <label for="chartType" style="font-weight:600; margin-right:8px;">Chart Type:</label>
                    <select id="chartType" class="chart-type-select">
                        <option value="bar">Bar</option>
                        <option value="pie">Pie</option>
                        <option value="line">Line</option>
                    </select>
                
                </div>
                <h3 style="margin-top:0;">Expenses by Category</h3>
                <div style="position:relative; width:100%; height:220px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            <div>
                <h3 style="margin-top:0;">Stats</h3>
                <p>Total Categories <?= count($categoryTotals) ?></p>
                <p>Total Expenses ₹<?= number_format($totalExpenses, 2) ?></p>
                <p>Monthly Budget ₹<?= (int)$monthly_budget ?></p>
                <p>Remaining Balance ₹<?= (int)($monthly_budget - $totalExpenses) ?></p>
                <?php
                $percentUsed = ($monthly_budget > 0) ? min(100, round(($totalExpenses / $monthly_budget) * 100)) : 0;
                ?>
                <?php if ($monthly_budget > 0): ?>
                <div class="budget-progress-container" style="margin:10px 0 0 0;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:14px; margin-bottom:2px;">
                        <span style="color:#4a90e2; font-weight:500;">Used: <?= $percentUsed ?>%</span>
                        <span style="color:#6b7280;">(<?= number_format($totalExpenses, 2) ?> / <?= (int)$monthly_budget ?>)</span>
                    </div>
                    <div class="budget-progress-bar" style="width:100%; height:14px; background:#e5e7eb; border-radius:7px; overflow:hidden;">
                        <div class="budget-progress-bar-inner" style="height:100%; width:<?= $percentUsed ?>%; background:
                            <?php if($percentUsed < 80): ?>#4a90e2<?php elseif($percentUsed < 100): ?>#f59e42<?php else: ?>#d0021b<?php endif; ?>;
                            border-radius:7px 0 0 7px; transition:width 0.4s;"></div>
                    </div>
                    <?php if ($percentUsed >= $budget_warn_limit && $percentUsed < 100): ?>
                    <div class="budget-warning-msg" style="margin-top:8px; color:#b45309; background:#fef3c7; border:1px solid #fde68a; border-radius:6px; padding:8px 12px; font-size:15px; display:flex; align-items:center; gap:8px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#f59e42;"></i>
                        <span>You have reached <?= $budget_warn_limit ?>% of your monthly budget. Please monitor your spending.</span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Filters -->
    <div class="filter-box">
        <select id="categoryFilter">
            <option value="">All Categories</option>
            <?php foreach($categoryTotals as $c): ?>
                <option><?= htmlspecialchars($c['category']) ?></option>
            <?php endforeach; ?>
        </select>

        <select id="monthFilter">
            <option value="">All Months</option>
            <?php
            for($m = 1; $m <= 12; $m++){
                echo "<option value='$m'>".date("F", mktime(0, 0, 0, $m, 1))."</option>";
            }
            ?>
        </select>

        <div class="date-range" style="display:flex; gap:6px; align-items:center;">
            <input type="date" id="startDate" style="flex:1;">
            <span>to</span>
            <input type="date" id="endDate" style="flex:1;">
        </div>

        <button onclick="applyFilter()">Filter</button>
    </div>

    <!-- Recent Expenses -->
    <section class="card">
        <h3 style="margin-top:0;">Recent Expenses</h3>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="expenseTable">
                    <?php foreach($recent as $r): ?>
                    <tr>
                        <td data-label="Date"><?= htmlspecialchars($r['date']) ?></td>
                        <td data-label="Title"><?= htmlspecialchars($r['title']) ?></td>
                        <td data-label="Category"><?= htmlspecialchars($r['category']) ?></td>
                        <td data-label="Amount"><?= number_format($r['amount'], 2) ?></td>
                        <td data-label="Action">
                            <a href="edit_expense.php?id=<?= $r['id'] ?>" class="action-icon edit" title="Edit">
                                <i class="fa-solid fa-pen-to-square"> </i>
                            </a>
                            <form method="post" action="delete_expense.php" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
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
    </section>

</div>

<script>
// Chart data
const categoryData = <?= json_encode(array_column($categoryTotals, 'total')) ?>;
const monthly_budget = <?= json_encode($monthly_budget) ?>;
const totalExpenses = <?= json_encode($totalExpenses) ?>;


const chartLabels = <?= json_encode(array_column($categoryTotals, 'category')) ?>;
let chartType = 'bar';
let categoryChart = null;

function getColors(type, data) {
    if(type === 'pie' || type === 'line') {
        // Pie/line: use a palette
        const palette = ['#4a90e2','#f5a623','#d0021b','#10b981','#a78bfa','#f472b6','#facc15','#38bdf8','#fb7185','#34d399'];
        return data.map((_,i) => palette[i%palette.length]);
    } else {
        // Bar: color by value
        return data.map(v => v > monthly_budget ? '#d0021b' : (v > monthly_budget*0.8 ? '#f5a623' : '#4a90e2'));
    }
}

function drawCategoryChart(type) {
    if(categoryChart) categoryChart.destroy();
    const ctx = document.getElementById('categoryChart').getContext('2d');
    const colors = getColors(type, categoryData);
    let options = {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{ display:type!=='bar' },
        },
    };
    let data = {
        labels: chartLabels,
        datasets: [{
            data: categoryData,
            backgroundColor: colors,
            borderRadius: type==='bar'?6:0
        }]
    };
    if(type==='bar') {
        options.plugins.annotation = {
            annotations:{
                line:{
                    type:'line',
                    yMin:monthly_budget,
                    yMax:monthly_budget,
                    borderColor:'red',
                    borderWidth:2,
                    label:{ enabled:true, content:'Monthly Budget' }
                }
            }
        };
        options.scales = { y:{ beginAtZero:true } };
    }
    if(type==='line') {
        data.datasets[0].fill = false;
        data.datasets[0].borderColor = '#4a90e2';
        data.datasets[0].backgroundColor = colors;
        data.datasets[0].tension = 0.3;
        options.scales = { y:{ beginAtZero:true } };
    }
    if(type==='pie') {
        options.plugins.datalabels = {
            color: '#222',
            font: { weight: 'bold', size: 14 },
            formatter: function(value, context) {
                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const percent = total ? (value / total * 100) : 0;
                return percent ? percent.toFixed(1) + '%' : '';
            }
        };
    } else {
        options.plugins.datalabels = false;
    }
    categoryChart = new Chart(ctx, {
        type: type,
        data: data,
        options: options,
        plugins: [ChartDataLabels]
    });
}

drawCategoryChart(chartType);

document.getElementById('chartType').addEventListener('change', function(e){
    chartType = e.target.value;
    drawCategoryChart(chartType);
});

// Theme toggle with icon
const themeBox = document.getElementById("themeToggleBox");
const updateThemeIcon = () => {
    if(document.body.classList.contains("dark")){
        themeBox.innerHTML = '<i class="fa-solid fa-sun"></i>';
    } else {
        themeBox.innerHTML = '<i class="fa-solid fa-moon"></i>';
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

// Over budget popup only
document.addEventListener("DOMContentLoaded", function(){
    const popupOverlay = document.getElementById("budgetPopupOverlay");
    const closeBtn = document.getElementById("closeBudgetPopup");
    if(totalExpenses > monthly_budget && monthly_budget > 0){
        if(popupOverlay && closeBtn){
            popupOverlay.style.display = "flex";
            closeBtn.addEventListener("click", function(){
                popupOverlay.style.display = "none";
            });
            popupOverlay.addEventListener("click", function(e){
                if(e.target === popupOverlay){
                    popupOverlay.style.display = "none";
                }
            });
        }
    }
});

// Simple filter stub
function applyFilter(){
    console.log("Filter clicked");
}

// Drawer nav
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
if(drawerOverlay){
    drawerOverlay.addEventListener("click", closeDrawer);
}
sidebarLinks.forEach(function(link){
    link.addEventListener("click", function(){
        if(window.innerWidth <= 768){
            closeDrawer();
        }
    });
});
</script>

</body>
</html>
