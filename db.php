<?php
// db.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// show errors only while developing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// load configuration array
$config = require __DIR__ . '/config.php';

$host     = $config['db_host'];
$dbname   = $config['db_name'];
$username = $config['db_user'];
$password = $config['db_pass'];

try {
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection error ' . $e->getMessage());
}

// helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin(){
    if(!isLoggedIn()){
        header('Location: index.php');
        exit;
    }
}
