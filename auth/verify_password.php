<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Consistent numeric role check (Admin = 1)
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

$status_msg = "";

if(isset($_POST['update_pass'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];

    if($new_pass !== $confirm_pass) {
        $status_msg = "mismatch";
    } else {
        // 2. SECURITY: Hash the password before saving to DB
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password=? WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $hashed_pass, $user_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $status_msg = "success";
        } else {
            $status_msg = "error";
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Security Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@500;700;900&display=swap');
        
        :root {
            --mims-primary: #2DD4BF; /* Electric Cyan */
            --mims-glow: rgba(45, 212, 191, 0.25);
            --bg-dark: #0F172A;     /* Deep Obsidian Navy */
            --glass: #1E293B;       /* Slate Glass */
            --text-main: #F8FAFC;
        }

        body, html { 
            height: 100%; margin: 0; font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }

        /* Neural Background Effect */
        .neural-sync {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.07) 0%, transparent 70%);
            z-index: -1;
        }

        .security-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 35px; padding: 40px;
            width: 100%; max-width: 440px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            z-index: 1;
        }

        .form-label {
            font-size: 0.75rem; font-weight: 700; color: #94A3B8;
            text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px; padding: 12px 15px;
            color: #ffffff; transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--mims-primary);
            box-shadow: 0 0 20px var(--mims-glow);
            outline: none; color: #ffffff;
        }

        .btn-update {
            background: var(--mims-primary);
            border: none; border-radius: 14px; padding: 18px;
            color: #0F172A; font-weight: 800; text-transform: uppercase;
            letter-spacing: 2px; width: 100%; margin-top: 10px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-update:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px var(--mims-glow);
            filter: brightness(1.1);
        }

        .security-badge {
            width: 70px; height: 70px; background: var(--mims-glow);
            color: var(--mims-primary); display: flex; align-items: center;
            justify-content: center; font-size: 30px; border-radius: 22px;
            margin: 0 auto 20px; border: 1px solid rgba(45, 212, 191, 0.2);
            box-shadow: 0 0 20px var(--mims-glow);
        }

        .mims-logo-mini {
            font-family: 'Outfit', sans-serif;
            font-weight: 900; color: var(--mims-primary);
            letter-spacing: 3px; margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="neural-sync"></div>

<div class="security-card animate__animated animate__zoomIn">
    <div class="text-center mb-4">
        <div class="mims-logo-mini small">MIMS SYSTEMS</div>
        <div class="security-badge">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h3 class="fw-bold mb-1">Security Update</h3>
        <p class="text-muted small">Elevated credentials for: <br>
            <span style="color: var(--mims-primary); font-weight: 800;"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
        </p>
    </div>

    <?php if($status_msg == "success"): ?>
        <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small animate__animated animate__tada mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> Security Protocol Updated.
        </div>
    <?php elseif($status_msg == "mismatch"): ?>
        <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning small mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> Passwords do not match.
        </div>
    <?php elseif($status_msg == "error"): ?>
        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger small mb-4">
            <i class="bi bi-x-circle-fill me-2"></i> System Error: Write access denied.
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="mb-4">
            <label class="form-label">Verify Password</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" name="update_pass" class="btn-update">
            Confirm Changes
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="dashboard.php" class="text-muted text-decoration-none small fw-bold" style="transition: 0.3s;" onmouseover="this.style.color='var(--mims-primary)'" onmouseout="this.style.color='#6c757d'">
            <i class="bi bi-arrow-left me-1"></i> Return to Terminal
        </a>
    </div>
</div>

</body>
</html>