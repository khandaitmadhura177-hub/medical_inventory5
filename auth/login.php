<?php
session_start();

// --- NEURAL GUARD: PREVENT BACK-BUTTON EXPLOIT ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('../config/db_connect.php');

if(isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // LOGIC: Direct Plain Text Comparison as requested
    $query = "SELECT * FROM users WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Secure the session ID transition
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // ROLE ROUTING: Correctly sending users to their respective sectors
        if($user['role'] == 1) {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../customer/dashboard.php");
        }
        exit();
    } else {
        $error = "Access Denied: Invalid credentials detected.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Secure Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&family=Outfit:wght@500;700;900&display=swap');

        :root {
            --mims-primary: #2DD4BF; /* Electric Cyan */
            --mims-accent: #FB7185;  /* Sunset Coral */
            --mims-glow: rgba(45, 212, 191, 0.25);
            --bg-dark: #0F172A;      /* Deep Obsidian Navy */
            --glass: #1E293B;        /* Slate Glass */
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
                        url('https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=2000');
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center;
            padding: 80px;
        }

        /* NEW BRANDING LAYOUT */
        .branding-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .brand-logo-img {
            height: 150px; /* Big size */
            width: auto;
            margin-bottom: 10px;
            filter: drop-shadow(0 0 15px var(--mims-glow));
        }

        .mims-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 6.5rem; font-weight: 900; line-height: 1;
            background: linear-gradient(to bottom, #ffffff, var(--mims-primary));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
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

        @keyframes pulse { 
            0% { opacity: 1; transform: scale(1); } 
            50% { opacity: 0.4; transform: scale(1.2); } 
            100% { opacity: 1; transform: scale(1); } 
        }

        .feature-item { 
            display: flex; align-items: center; gap: 15px; 
            margin-bottom: 25px; font-size: 1.1rem; color: rgba(248, 250, 252, 0.7);
            text-align: left;
        }
        .feature-item i { color: var(--mims-primary); font-size: 1.5rem; }

        .form-side {
            flex: 1; background: var(--bg-dark);
            display: flex; align-items: center; justify-content: center;
            padding: 40px; border-left: 1px solid rgba(45, 212, 191, 0.1);
        }

        .glass-card {
            width: 100%; max-width: 440px;
            padding: 50px; border-radius: 35px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 40px 80px rgba(0,0,0,0.6);
        }

        .form-label {
            font-size: 0.75rem; font-weight: 700; color: #94A3B8;
            text-transform: uppercase; letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .password-wrapper {
            position: relative;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px; padding: 15px;
            color: #ffffff; transition: 0.3s;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: var(--mims-primary);
            box-shadow: 0 0 20px var(--mims-glow);
            outline: none; color: #ffffff;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--mims-primary);
            font-size: 1.2rem;
            z-index: 10;
        }

        .btn-login {
            background: var(--mims-primary);
            border: none; border-radius: 14px; padding: 18px;
            color: #0F172A; font-weight: 800; text-transform: uppercase;
            letter-spacing: 3px; width: 100%; margin-top: 30px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-login:hover {
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
                AI SYSTEMS AUTHORIZED
            </div>
            
            <div class="feature-list">
                <div class="feature-item">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Secure Store Phasing Active</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-robot"></i>
                    <span>AI-Integrated Data Analysis</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-hdd-network-fill"></i>
                    <span>Encrypted Inventory Infrastructure</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-lightning-fill"></i>
                    <span>High-Performance Store Logic</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="glass-card animate__animated animate__fadeInRight">
            <div class="mb-5">
                <h2 class="fw-bold mb-1">Authorization</h2>
                <p class="text-muted small">Enter your digital credentials to continue.</p>
            </div>

            <?php if(isset($error)): ?> 
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-3 py-2 small mb-4">
                    <i class="bi bi-shield-x me-2"></i> <?php echo $error; ?>
                </div> 
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label class="form-label">Network Email</label>
                    <input type="email" name="email" class="form-control" placeholder="admin@mims.com" required>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label class="form-label">Master Password</label>
                        <a href="forget_password.php" class="text-decoration-none small" style="color: var(--mims-primary);">Forgot?</a>
                    </div>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="passwordField" class="form-control" placeholder="••••••••" required>
                        <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" name="login" class="btn-login">
                    Authorize Access
                </button>
            </form>

            <div class="text-center mt-5">
                <p class="small text-muted mb-0">Don't have clearance?</p>
                <a href="register.php" class="text-decoration-none fw-bold" style="color: var(--mims-primary);">Create Identity</a>
            </div>
        </div>
    </div>
</div>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#passwordField');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('bi-eye');
        this.classList.toggle('bi-eye-slash');
    });
</script>

</body>
</html>