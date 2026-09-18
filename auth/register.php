<?php
session_start();
include('../config/db_connect.php');

if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = 0; 

    $check_user = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $check_user);

    if(mysqli_num_rows($result) > 0) {
        $error = "Identity trace found: Email already registered.";
    } else {
        $query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";
        if(mysqli_query($conn, $query)) {
            $_SESSION['reg_success'] = "Identity Initialized!";
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Initialize Identity</title>
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

        .brand-logo-img {
            height: 120px; width: auto; margin-bottom: 10px;
            filter: drop-shadow(0 0 15px var(--mims-glow));
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
            padding: 40px 50px; border-radius: 35px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 40px 80px rgba(0,0,0,0.6);
        }

        .form-label {
            font-size: 0.75rem; font-weight: 700; color: #94A3B8;
            text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;
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

        .btn-register {
            background: var(--mims-primary);
            border: none; border-radius: 14px; padding: 18px;
            color: #0F172A; font-weight: 800; text-transform: uppercase;
            letter-spacing: 2px; width: 100%; margin-top: 20px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-register:hover {
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
            <img src="../assets/image/medicine/logo.png" alt="Logo" class="brand-logo-img">
            <h1 class="mims-logo">MIMS</h1>
            <div class="ai-status">
                <div class="pulse-dot"></div>
                Identity Protocol Alpha
            </div>
            <p class="text-muted">Global Neural Network Registration</p>
        </div>
    </div>

    <div class="form-side">
        <div class="glass-card animate__animated animate__fadeInRight">
            <div class="mb-4">
                <h2 class="fw-bold mb-1">Registration</h2>
                <p class="text-muted small">Initialize your digital credentials.</p>
            </div>

            <?php if(isset($error)): ?> 
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 py-2 small mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                </div> 
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" placeholder="Agent_01" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Network Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@mims.com" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Access Key</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" name="register" class="btn-register">
                    Initialize Account
                </button>
            </form>

            <div class="text-center mt-4">
                <p class="small text-muted mb-0">Already have clearance?</p>
                <a href="login.php" class="text-decoration-none fw-bold" style="color: var(--mims-primary);">Secure Login</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>