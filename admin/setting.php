<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Only Admin (Role 1)
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

date_default_timezone_set('Asia/Kolkata');

$message = "";
$error = "";

// 2. LOGIC: Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sync_core'])) {
    $shop_name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $currency = mysqli_real_escape_string($conn, $_POST['currency']);
    $timezone = mysqli_real_escape_string($conn, $_POST['timezone']);
    
    // Check if table has a row
    $check = mysqli_query($conn, "SELECT id FROM settings WHERE id=1");
    
    if(mysqli_num_rows($check) > 0) {
        $query = "UPDATE settings SET shop_name='$shop_name', currency='$currency', timezone='$timezone' WHERE id=1";
    } else {
        $query = "INSERT INTO settings (id, shop_name, currency, timezone) VALUES (1, '$shop_name', '$currency', '$timezone')";
    }

    if(mysqli_query($conn, $query)) {
        $message = "Core configuration synchronized successfully!";
    } else {
        $error = "Kernel Error: Write access denied to system registry.";
    }
}

// 3. LOGIC: Handle Admin Password Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_security'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if(!empty($new_pass) && $new_pass === $confirm_pass) {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $u_id = $_SESSION['user_id'];
        $update_pass = "UPDATE users SET password='$hashed_pass' WHERE id='$u_id'";
        
        if(mysqli_query($conn, $update_pass)) {
            $message = "Security protocols updated. Password reset successful.";
        }
    } else {
        $error = "Logic Mismatch: Passwords do not correlate.";
    }
}

/** * ERROR PREVENTION: Wrap the fetch in a check 
 */
$res = mysqli_query($conn, "SELECT * FROM settings WHERE id=1");
$current = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | System Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        :root { 
            --bg: #060709; --primary-neon: #00d2ff; --secondary-neon: #3a7bd5; 
            --card-bg: #0f1115; --input-bg: rgba(255, 255, 255, 0.03); 
            --border: rgba(0, 210, 255, 0.15);
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); 
            background-image: radial-gradient(circle at 50% 50%, rgba(0, 210, 255, 0.05) 0%, transparent 80%);
            color: #ffffff; min-height: 100vh;
        }
        .glass-card { background: var(--card-bg); border-radius: 40px; border: 1px solid var(--border); box-shadow: 0 40px 100px rgba(0,0,0,0.8); overflow: hidden; margin-bottom: 2rem; }
        .settings-header { background: linear-gradient(135deg, var(--primary-neon), var(--secondary-neon)); color: #000; padding: 30px; display: flex; align-items: center; gap: 20px; }
        .form-control, .form-select { border: 1px solid var(--border); border-radius: 18px; padding: 12px 20px; background: var(--input-bg); color: white; transition: 0.3s; }
        .form-control:focus { background: rgba(255,255,255,0.05); border-color: var(--primary-neon); color: white; box-shadow: none; }
        label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--primary-neon); margin-bottom: 10px; display: block; }
        .btn-save { background: linear-gradient(90deg, #00d2ff, #3a7bd5); color: #000; border: none; border-radius: 20px; padding: 15px; font-weight: 800; text-transform: uppercase; transition: 0.4s; }
        .btn-save:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0, 210, 255, 0.3); }
        .status-pill { background: rgba(0, 210, 255, 0.1); color: var(--primary-neon); border: 1px solid var(--border); font-size: 0.7rem; font-weight: 700; }
        .back-link { text-decoration: none; color: rgba(255,255,255,0.4); font-weight: 700; }
        .alert { border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <a href="dashboard.php" class="back-link"><i class="bi bi-arrow-left me-2"></i> Exit to Dashboard</a>
                <span class="badge rounded-pill status-pill px-3 py-2">KERNEL v2.0.4 - STABLE</span>
            </div>

            <div class="glass-card animate__animated animate__fadeIn">
                <div class="settings-header">
                    <i class="bi bi-gear-wide-connected fs-3"></i>
                    <h4 class="fw-800 mb-0">System Core</h4>
                </div>
                <div class="p-4">
                    <?php if($message): ?>
                        <div class="alert alert-info bg-dark text-info border-info small fw-bold"><i class="bi bi-cpu-fill me-2"></i><?php echo $message; ?></div>
                    <?php endif; ?>
                    <?php if($error): ?>
                        <div class="alert alert-danger bg-dark text-danger border-danger small fw-bold"><i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="row g-4">
                            <div class="col-12">
                                <label>Pharmacy Designation</label>
                                <input type="text" name="shop_name" class="form-control" value="<?php echo $current['shop_name'] ?? 'MIMS PHARMACY'; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label>Base Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="INR" <?php if(($current['currency'] ?? '') == 'INR') echo 'selected'; ?>>INR (₹)</option>
                                    <option value="USD" <?php if(($current['currency'] ?? '') == 'USD') echo 'selected'; ?>>USD ($)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Timezone</label>
                                <select name="timezone" class="form-select">
                                    <option value="Asia/Kolkata" <?php if(($current['timezone'] ?? '') == 'Asia/Kolkata') echo 'selected'; ?>>Asia/Kolkata</option>
                                    <option value="UTC" <?php if(($current['timezone'] ?? '') == 'UTC') echo 'selected'; ?>>UTC</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="sync_core" class="btn-save w-100">Commit Core Sync</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="glass-card animate__animated animate__fadeIn" style="animation-delay: 0.2s;">
                <div class="settings-header" style="background: linear-gradient(135deg, #ff416c, #ff4b2b);">
                    <i class="bi bi-shield-lock fs-3"></i>
                    <h4 class="fw-800 mb-0">Security Override</h4>
                </div>
                <div class="p-4">
                    <form action="" method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label>New Admin Password</label>
                                <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="col-md-6">
                                <label>Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_security" class="btn-save w-100" style="background: linear-gradient(90deg, #ff416c, #ff4b2b);">Update Security Token</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>