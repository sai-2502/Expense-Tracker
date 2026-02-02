<?php
session_start();
require_once 'db.php';

$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Expense Tracker</title>
<style>
* {
    box-sizing: border-box;
}

/* Full screen centered background */
body {
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: linear-gradient(135deg, #4a90e2, #50e3c2);
    font-family: Arial, sans-serif;
    padding: 20px;
}

/* Card styling with animation */
.card {
    background:#fff;
    padding:40px 30px;
    border-radius:12px;
    width:100%;
    max-width:380px;
    text-align:center;
    box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    transform: translateY(-40px);
    opacity:0;
    animation: slideFadeIn 0.8s forwards;
}

@keyframes slideFadeIn {
    to { transform: translateY(0); opacity:1; }
}

/* Inputs */
input {
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:6px;
    box-sizing:border-box;
    transition:0.3s;
    font-size:15px;
}
input:focus {
    border-color:#4a90e2;
    box-shadow: 0 0 8px rgba(74,144,226,0.3);
    outline:none;
}

/* Button */
button {
    width:100%;
    padding:12px;
    margin-top:10px;
    background:#4a90e2;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:600;
    transition:0.3s;
    font-size:15px;
}
button:hover {
    background:#357ABD;
}

/* Error message */
.error {
    color:red;
    font-size:14px;
    margin-bottom:10px;
    animation: shake 0.5s;
}

/* Shake animation for error */
@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    50% { transform: translateX(5px); }
    75% { transform: translateX(-5px); }
    100% { transform: translateX(0); }
}

/* Register link */
p {
    margin-top:15px;
    font-size:14px;
}
p a {
    color:#4a90e2;
    text-decoration:none;
    font-weight:600;
}
p a:hover {
    text-decoration:underline;
}

/* Heading */
h2 {
    margin-top:0;
    margin-bottom:10px;
    font-size:24px;
}

/* ------------------------ */
/*     RESPONSIVE DESIGN    */
/* ------------------------ */

/* Small phones under 400 */
@media (max-width: 400px) {
    body {
        padding: 10px;
    }
    .card {
        padding: 24px 18px;
        max-width: 100%;
        border-radius:10px;
    }
    input, button {
        padding:10px;
        font-size:14px;
    }
    h2 {
        font-size:20px;
    }
}

/* Phones 401 to 480 */
@media (max-width: 480px) and (min-width: 401px) {
    body {
        padding: 16px;
    }
    .card {
        padding: 28px 20px;
        max-width: 95%;
    }
    input, button {
        padding: 10px;
        font-size:14px;
    }
    h2 {
        font-size:22px;
    }
}

/* Tablets 481 to 768 */
@media (max-width: 768px) and (min-width: 481px) {
    body {
        padding: 30px;
    }
    .card {
        max-width: 420px;
        padding: 35px 28px;
    }
    input, button {
        padding: 12px;
        font-size:15px;
    }
    h2 {
        font-size:24px;
    }
}

/* Small laptops 769 to 1199 */
@media (min-width: 769px) and (max-width: 1199px) {
    body {
        padding: 30px;
    }
    .card {
        max-width: 400px;
    }
}

/* Large screens 1200 and above */
@media (min-width: 1200px) {
    .card {
        max-width: 430px;
        padding: 45px 35px;
    }
    input, button {
        font-size:16px;
    }
    h2 {
        font-size:26px;
    }
}
</style>
</head>
<body>
  <div class="card">
    <h2>Login</h2>
    <?php if(!empty($error)): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
      <input name="email" type="email" placeholder="Email" required>
      <input name="password" type="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    <p>No account? <a href="register.php">Register</a></p>
  </div>
</body>
</html>
