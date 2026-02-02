<?php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "Passwords do not match";
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = "Email already registered";
        } else {
            // Hash password
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (name, email, password) VALUES (?, ?, ?)"
            );

            if ($stmt->execute([$name, $email, $hashed])) {
                $_SESSION['success'] = "Registration successful. Please log in.";
                header("Location: index.php");
                exit;
            } else {
                $error = "Registration failed. Try again!";
            }
        }
    }
}
?>


<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Expense Tracker</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');
* { box-sizing: border-box; }
body {
    margin:0;
    min-height:100vh;
    display:flex;
    align-items:flex-start;
    justify-content:center;
    font-family:'Inter', sans-serif;
    background: linear-gradient(135deg,#4a90e2,#50e3c2);
    overflow-x:hidden;
    padding-top:50px;
}
.success {
    background:#e6fffa;
    color:#065f46;
    padding:10px;
    border-radius:6px;
    margin-bottom:12px;
    font-size:14px;
}

.card {
    background:#fff;
    padding:50px 35px;
    border-radius:15px;
    width:100%;
    max-width:400px;
    text-align:center;
    box-shadow:0 20px 50px rgba(0,0,0,0.15);
}
.card h2 { margin-bottom:25px; font-weight:600; color:#111; }
input {
    width:100%;
    padding:14px 18px;
    margin:12px 0;
    border:1px solid #e2e8f0;
    border-radius:8px;
    font-size:16px;
    transition:0.3s;
}
input:focus {
    border-color:#4a90e2;
    box-shadow:0 0 10px rgba(74,144,226,0.3);
    outline:none;
}
button {
    width:100%;
    padding:14px;
    margin-top:15px;
    border:none;
    border-radius:8px;
    background:#4a90e2;
    color:#fff;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover {
    background:#357ab8;
    transform: translateY(-2px);
    box-shadow:0 6px 12px rgba(0,0,0,0.15);
}
.error {
    background:#ffe7e7;
    color:#b42318;
    padding:10px;
    border-radius:6px;
    margin-bottom:10px;
    font-size:14px;
}
.card p {
    margin-top:18px;
    font-size:14px;
}
.card p a {
    color:#4a90e2;
    text-decoration:none;
    font-weight:500;
}
.card p a:hover { text-decoration:underline; }
@media(max-width:480px){
    .card { padding:35px 25px; max-width:95%; }
    input, button { font-size:15px; padding:12px 15px; }
    h2 { font-size:22px; }
}
</style>
</head>
<body>
<div class="card">
    <h2>Create Account</h2>
    <?php if(!empty($error)): ?><div class="error"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
        <input name="name" placeholder="Full Name" required>
        <input name="email" type="email" placeholder="Email" required>
        <input name="password" type="password" placeholder="Password" required>
        <input name="confirm_password" type="password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
    <p>Have an account? <a href="index.php">Login</a></p>
</div>
</body>
</html>