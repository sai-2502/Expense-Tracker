<?php
session_start();
require_once "db.php";
requireLogin();

$userId = $_SESSION['user_id'];

// File name
$filename = "expenses_report_" . date("Y-m-d_H-i-s") . ".csv";

// Send headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

// Open output stream
$output = fopen("php://output", "w");

// CSV Header
fputcsv($output, ["Date", "Title", "Category", "Amount", "Notes"]);

// Fetch user expenses
$stmt = $pdo->prepare("SELECT date, title, category, amount, notes FROM expenses WHERE user_id=? ORDER BY date DESC");
$stmt->execute([$userId]);

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
