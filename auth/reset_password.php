<?php
session_start();
include('../config/db_connect.php');

// Redirect if no session exists from the logic page
if(!isset($_SESSION['reset_email'])) {
    header("Location: forget_password.php");
    exit();
}

$status_msg = "";

if(isset($_POST['update_password'])){
    $new_pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    if($new_pass !== $confirm_pass) {
        $status_msg = "mismatch";
    } else {
        // Hashing for security
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed_pass, $email);

        if($stmt->execute()){
            unset($_SESSION['reset_email']);
            header("Location: login.php?msg=password_reset_success");
            exit();
        } else {
            $status_msg = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Define New Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
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
            height: 100%; margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main); overflow: hidden;
        }

        .neural-sync {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.07) 0%, transparent 70%);
            z-index: -1; pointer-events: none;
        }

        .main-wrapper { display: flex; height: 100vh; width: 100vw; position: relative; z-index: 1; }

        .brand-side {
            flex: 1.4;
            position: relative;
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85)), 
                        url('https://images.unsplash.com/photo-1576091160550-2173dba999ef?q=80&w=2000');
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center;
            padding: 80px;
        }

        .branding-container {
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }

        .mims-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 6.5rem; font-weight: 900; line-height: 1;
            background: linear-gradient(to bottom, #ffffff, var(--mims-primary));
            background-clip: text; -webkit-background-clip: text;
            -webkit-text-fill-color: transparent; margin-bottom: 20px;
        }

        .ai-status {
            font-size: 0.85rem; letter-spacing: 5px; color: var(--mims-primary);
            font-weight: 700; text-transform: uppercase; margin-bottom: 40px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }

        .pulse-dot {
            width: 10px; height: 10px; background: var(--mims-primary);
            border-radius: 50%; box-shadow: 0 0 15px var(--mims-primary);
            animation: pulse 2s infinite;
        }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.4; } 100% { opacity: 1; } }

        .form-side {
            flex: 1; background: var(--bg-dark);
            display: flex; align-items: center; justify-content: center;
            padding: 40px; border-left: 1px solid rgba(45, 212, 191, 0.1);
        }

        .glass-card {
            width: 100%; max-width: 440px;
            padding: 50px; border-radius: 35px;
            background: var(--glass); backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 40px 80px rgba(0,0,0,0.6);
        }

        .form-label {
            font-size: 0.75rem; font-weight: 700; color: #94A3B8;
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px; padding: 15px;
            color: #fff; transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--mims-primary);
            box-shadow: 0 0 20px var(--mims-glow);
            outline: none; color: #fff;
        }

        .btn-reset {
            background: var(--mims-primary);
            border: none; border-radius: 14px; padding: 18px;
            color: #0F172A; font-weight: 800; text-transform: uppercase;
            letter-spacing: 3px; width: 100%; margin-top: 30px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-reset:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px var(--mims-glow);
            filter: brightness(1.1);
        }

        @media (max-width: 992px) { .brand-side { display: none; } }
    </style>
</head>
<body>

<div class="neural-sync"></div>

<div class="main-wrapper">
    <div class="brand-side">
        <div class="animate__animated animate__fadeInLeft branding-container">
            <h1 class="mims-logo">MIMS</h1>
            <div class="ai-status">
                <div class="pulse-dot"></div>
                OVERWRITE CREDENTIALS
            </div>
            <p class="text-white-50">Authorized reset for: <br>
            <span style="color: var(--mims-primary); font-weight: 800;"><?php echo htmlspecialchars($_SESSION['reset_email']); ?></span></p>
        </div>
    </div>

    <div class="form-side">
        <div class="glass-card animate__animated animate__fadeInRight">
            <div class="mb-5">
                <h2 class="fw-bold mb-1">Set New Access</h2>
                <p class="text-muted small">Establish your new master password.</p>
            </div>

            <?php if($status_msg == "mismatch"): ?>
                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-warning rounded-3 py-2 small mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i> Passwords do not match.
                </div>
            <?php elseif($status_msg == "error"): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 py-2 small mb-4">
                    <i class="bi bi-x-circle me-2"></i> System error. Try again.
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                </div>

                <div class="mb-4">
                    <label class="form-label">Verify Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>

                <button type="submit" name="update_password" class="btn-reset">
                    Confirm Reset
                </button>
            </form>

            <div class="text-center mt-5">
                <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--mims-primary);">Return to Portal</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>