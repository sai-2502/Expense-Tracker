<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Handle contact form submission
$successMsg = '';
$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        try {
            $stmt = $pdo->prepare('INSERT INTO support_messages (user_id, name, email, message) VALUES (?, ?, ?, ?)');
            $stmt->execute([$userId, $name, $email, $message]);
            $successMsg = 'Thank you for contacting us! We will get back to you soon.';
        } catch(Exception $e) {
            $errorMsg = 'Failed to send your message. Please try again.';
        }
    } else {
        $errorMsg = 'Please fill in all fields.';
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Help & Support</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/nav.css">
<link rel="stylesheet" href="assets/css/dark-mode.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.support-container { max-width: 480px; margin: 32px auto; background: var(--card-bg,#fff); border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 32px 24px; }
.support-container h2 { text-align:center; margin-bottom:18px; }
.support-container form { display:flex; flex-direction:column; gap:14px; }
.support-container input, .support-container textarea { width:100%; padding:10px; border-radius:6px; border:1.2px solid #d1d5db; font-size:15px; }
.support-container textarea { min-height: 90px; }
.support-container button { padding:10px; border-radius:6px; background:#2563eb; color:#fff; border:none; font-size:16px; font-weight:500; cursor:pointer; transition:background 0.18s; }
.support-container button:hover { background:#174bbd; }
.success-msg, .error-msg { margin-bottom:10px; text-align:center; font-size:15px; border-radius:6px; padding:7px 14px; }
.success-msg { color:#1a7f37; background:#e6f9ed; border:1px solid #b6e7c9; }
.error-msg { color:#b91c1c; background:#fbeaea; border:1px solid #f5c2c7; }
body.dark .support-container { background:#23272f; color:#f3f4f6; }
body.dark .support-container input, body.dark .support-container textarea { background:#181a20; color:#f3f4f6; border:1.2px solid #374151; }
body.dark .support-container button { background:#4a90e2; }
body.dark .support-container button:hover { background:#2563eb; }
</style>
</head>
<body>
<?php include 'nav.php'; ?>
<div class="main">
    <div class="support-container">
        <h2><i class="fa-solid fa-circle-info"></i> Help & Support</h2>
        <p style="text-align:center; margin-bottom:18px;">Need help or have a question? Fill out the form below and our team will assist you as soon as possible.</p>
        <?php if($successMsg): ?>
            <div class="success-msg" id="supportSuccessMsg"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
            <div class="error-msg" id="supportErrorMsg"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="text" name="name" placeholder="Your Name" value="<?= htmlspecialchars($user['name']) ?>" required>
            <input type="email" name="email" placeholder="Your Email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <textarea name="message" placeholder="How can we help you?" required></textarea>
            <button type="submit"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
        </form>
    </div>
</div>
<script>
setTimeout(function(){
    const msgIds = ['supportSuccessMsg','supportErrorMsg'];
    msgIds.forEach(function(id){
        const el = document.getElementById(id);
        if(el){ el.style.display = 'none'; }
    });
}, 2000);
</script>
</body>
</html>
