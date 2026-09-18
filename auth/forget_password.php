<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Account Recovery</title>
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

        .feature-item { 
            display: flex; align-items: center; gap: 15px; 
            margin-bottom: 25px; font-size: 1.1rem; color: rgba(248, 250, 252, 0.7);
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
            text-transform: uppercase; letter-spacing: 2px; margin-bottom: 10px;
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

        .btn-recovery {
            background: var(--mims-primary);
            border: none; border-radius: 14px; padding: 18px;
            color: #0F172A; font-weight: 800; text-transform: uppercase;
            letter-spacing: 3px; width: 100%; margin-top: 30px;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-recovery:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px var(--mims-glow);
            filter: brightness(1.1);
        }

        .back-link {
            text-decoration: none;
            color: rgba(248, 250, 252, 0.5);
            font-weight: 700; font-size: 0.9rem; transition: 0.3s;
        }

        .back-link:hover { color: var(--mims-primary); }

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
                RECOVERY PROTOCOL ACTIVE
            </div>
            
            <div class="feature-list">
                <div class="feature-item">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Secure Identity Verification</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-envelope-check-fill"></i>
                    <span>Encrypted Link Dispatch</span>
                </div>
                <div class="feature-item">
                    <i class="bi bi-key-fill"></i>
                    <span>Access Restoration Logic</span>
                </div>
            </div>
        </div>
    </div>

    <div class="form-side">
        <div class="glass-card animate__animated animate__fadeInRight">
            <div class="mb-5">
                <h2 class="fw-bold mb-1">Reset Password</h2>
                <p class="text-muted small">Enter your email to receive recovery steps.</p>
            </div>

            <?php if(isset($_GET['error']) && $_GET['error'] == 'not_found'): ?>
                <div class="alert alert-danger border-0 bg-danger bg-opacity-10 text-danger rounded-4 py-2 small mb-4">
                    <i class="bi bi-exclamation-circle me-2"></i> Email address not found.
                </div>
            <?php endif; ?>

            <form action="reset_logic.php" method="POST">
                <div class="mb-4">
                    <label class="form-label">Network Email</label>
                    <input type="email" name="email" class="form-control" placeholder="yourname@pharmacy.com" required>
                </div>

                <button type="submit" class="btn-recovery">
                    Send Instructions
                </button>
            </form>

            <div class="text-center mt-5">
                <a href="login.php" class="back-link">
                    <i class="bi bi-arrow-left me-2"></i> Return to Sign In
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>