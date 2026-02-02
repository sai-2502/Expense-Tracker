<?php
session_start();
require_once 'db.php';
requireLogin();
$userId = $_SESSION['user_id'];

// Handle form submission
$successMsg = '';
$errorMsg = '';
$fileSelectedMsg = '';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $profileUpdated = false;
    $imageUploaded = false;
    try {
        if(!empty($_FILES['profile_pic']['name'])){
            if($_FILES['profile_pic']['error'] === UPLOAD_ERR_OK){
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['profile_pic']['tmp_name']);
                finfo_close($finfo);
                $allowedMime = ['image/jpeg','image/pjpeg','image/png','image/gif','image/webp'];
                if(!in_array(strtolower($mime), $allowedMime)){
                    $errorMsg = "Invalid file type: $mime";
                } else {
                    $extMap = [
                        'image/jpeg'=>'jpg',
                        'image/pjpeg'=>'jpg',
                        'image/png'=>'png',
                        'image/gif'=>'gif',
                        'image/webp'=>'webp'
                    ];
                    $ext = $extMap[strtolower($mime)];
                    $uploadDir = __DIR__ . '/uploads/profile_pics/';
                    if(!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
                    $filename = uniqid() . '.' . $ext;
                    if(move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $filename)){
                        $stmt = $pdo->prepare('UPDATE users SET name=?, profile_pic=? WHERE id=?');
                        $stmt->execute([$name, $filename, $userId]);
                        $imageUploaded = true;
                        $profileUpdated = true;
                    } else {
                        $errorMsg = "Failed to upload file. Please try again.";
                    }
                }
            } else {
                $errorMsg = "Upload error: " . ($_FILES['profile_pic']['error'] === 1 ? 'File too large.' : $_FILES['profile_pic']['error']);
            }
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name=? WHERE id=?');
            $stmt->execute([$name, $userId]);
            $profileUpdated = true;
        }
        if($profileUpdated && !$errorMsg){
            if($imageUploaded){
                $successMsg = "Photo uploaded successfully!";
            } else {
                $successMsg = "Profile updated successfully!";
            }
        }
    } catch(Exception $e) {
        $errorMsg = "An unexpected error occurred. Please try again.";
    }
}

// Fetch user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profile</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/profile.css">
<link rel="stylesheet" href="assets/css/dark-mode.css">
<link rel="stylesheet" href="assets/css/nav.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">    
</head>
<body>

<?php include 'nav.php'; ?>

<!-- Main -->
<div class="main">
    <div class="card">
        <h2>Profile</h2>
        <div style="position:relative; display:flex; flex-direction:column; align-items:center; gap:18px;">
            <?php
                // Refetch user data after update for instant UI update
                $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
            <img src="<?= $user['profile_pic'] ? 'uploads/profile_pics/' . htmlspecialchars($user['profile_pic']) . '?t=' . time() : 'assets/img/default.png' ?>" alt="profile" class="avatar" style="width:110px; height:110px; object-fit:cover; border-radius:50%; border:3px solid #e5e7eb; margin-bottom:10px;">
            <?php if($successMsg): ?>
                <div class="success-msg" id="profileSuccessMsg" style="color:#1a7f37; background:#e6f9ed; border:1px solid #b6e7c9; border-radius:6px; padding:7px 14px; margin-bottom:8px; font-size:15px; text-align:center;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>
            <?php if($errorMsg): ?>
                <div class="error-msg" id="profileErrorMsg" style="color:#b91c1c; background:#fbeaea; border:1px solid #f5c2c7; border-radius:6px; padding:7px 14px; margin-bottom:8px; font-size:15px; text-align:center;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data" style="width:100%; max-width:340px; display:flex; flex-direction:column; align-items:center; gap:12px;">
                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" placeholder="Your Name" required style="width:100%;">
                <label for="profilePicInput" class="upload-photo-label">
                    <i class="fa-solid fa-upload"></i> <span id="uploadLabelText">Upload Photo</span>
                    <input id="profilePicInput" type="file" name="profile_pic" accept="image/*" style="display:none;">
                </label>
                <div id="fileSelectedMsg" style="font-size:13px; color:#2563eb; margin-bottom:0; display:none;"></div>
                <!-- CSS moved to assets/css/profile.css -->
                <button type="submit" style="width:100%;">Save</button>
            </form>
        </div>
        <div style="margin-top:18px; text-align:center;">
            <div class="profile-email" style="margin-bottom:8px; font-size:15px;"><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></div>
        </div>
        <div style="margin-top:28px; justify-content:center;">
            <div id="passwordAlertWrapper">
            <?php if(isset($_GET['password']) && $_GET['password'] === 'success'): ?>
                <div class="success-msg" id="passwordSuccessMsg" style="color:#1a7f37; background:#e6f9ed; border:1px solid #b6e7c9; border-radius:6px; padding:7px 14px; margin-bottom:8px; font-size:15px; text-align:center;">
                    <i class="fa-solid fa-circle-check"></i> Password updated successfully!
                </div>
            <?php elseif(isset($_GET['password']) && $_GET['password'] === 'fail'): ?>
                <div class="error-msg" id="passwordErrorMsg" style="color:#b91c1c; padding:5px 10px; margin-bottom:8px; font-size:15px; text-align:center; display:flex; align-items:center; justify-content:center; gap:6px; min-height:unset;">
                    <i class="fa-solid fa-circle-exclamation"></i> <span>
                    <?php
                        $type = $_GET['type'] ?? '';
                        if ($type === 'incorrect') {
                            echo 'Current password is incorrect.';
                        } elseif ($type === 'short') {
                            echo 'New password must be at least 6 characters.';
                        } elseif ($type === 'nomatch') {
                            echo 'New password and confirm password do not match.';
                        } else {
                            echo 'Failed to update password. Please try again.';
                        }
                    ?>
                    </span>
                </div>
            <?php endif; ?>
            </div>
            <form method="post" action="change_password.php" style="width:100%; max-width:340px; display:flex; flex-direction:column; gap:12px; align-items:center;">
                <h3 style="margin:0 0 8px 0; font-size:1.1em;">Change Password</h3>
                <input type="password" name="current_password" placeholder="Current Password" required style="width:100%;">
                <input type="password" name="new_password" placeholder="New Password" required style="width:100%;">
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required style="width:100%;">
                <button type="submit" style="width:100%;">Update Password</button>
            </form>
        </div>
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

menuButton.addEventListener("click", function(){
    if(sidebar.classList.contains("open")){
        closeDrawer();
    } else {
        openDrawer();
    }
});

drawerOverlay.addEventListener("click", closeDrawer);

sidebarLinks.forEach(function(link){
    link.addEventListener("click", function(){
        if(window.innerWidth <= 768){
            closeDrawer();
        }
    });
});

// Show selected file name for profile image
const profilePicInput = document.getElementById('profilePicInput');
const fileSelectedMsg = document.getElementById('fileSelectedMsg');
const uploadLabelText = document.getElementById('uploadLabelText');
if(profilePicInput && fileSelectedMsg && uploadLabelText){
    profilePicInput.addEventListener('change', function(){
        if(this.files && this.files[0]){
            fileSelectedMsg.style.display = 'block';
            fileSelectedMsg.textContent = 'Selected: ' + this.files[0].name;
            uploadLabelText.textContent = 'Change Photo';
        } else {
            fileSelectedMsg.style.display = 'none';
            uploadLabelText.textContent = 'Upload Photo';
        }
    });
}

// Auto-hide all success/error messages after 2 seconds
setTimeout(function(){
    const msgIds = ['profileSuccessMsg','profileErrorMsg','passwordSuccessMsg','passwordErrorMsg'];
    msgIds.forEach(function(id){
        const el = document.getElementById(id);
        if(el){
            el.style.display = 'none';
        }
    });
    // Also clear the wrapper to prevent duplicate alerts
    const pwAlert = document.getElementById('passwordAlertWrapper');
    if(pwAlert){
        pwAlert.innerHTML = '';
    }
}, 2000);
</script>

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
