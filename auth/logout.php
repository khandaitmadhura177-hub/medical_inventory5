<?php
session_start();

// --- NEURAL GUARD: PREVENT CACHING AFTER LOGOUT ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 1. Clear all session variables from memory
session_unset();
$_SESSION = array();

// 2. Destroy the session cookie for maximum security
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Securing Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
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
            display: flex; align-items: center; justify-content: center;
            color: var(--text-main); text-align: center;
            overflow: hidden;
        }

        /* Consistent Background with Login */
        .neural-sync {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.07) 0%, transparent 70%);
            z-index: -1; pointer-events: none;
        }

        .logout-box {
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 60px 40px; 
            border-radius: 35px;
            border: 1px solid rgba(45, 212, 191, 0.1);
            max-width: 450px; width: 90%;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
        }

        .loader-wrapper {
            position: relative;
            width: 80px; height: 80px;
            margin: 0 auto 30px;
        }

        .loader {
            width: 80px; height: 80px;
            border: 4px solid rgba(255, 255, 255, 0.05);
            border-top: 4px solid var(--mims-primary);
            border-right: 4px solid var(--mims-primary);
            border-radius: 50%;
            animation: spin 1s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
        }

        .loader-icon {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            color: var(--mims-primary);
            animation: pulse 1.5s infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: translate(-50%, -50%) scale(1); filter: drop-shadow(0 0 5px var(--mims-primary)); }
            50% { opacity: 0.5; transform: translate(-50%, -50%) scale(0.9); }
        }

        .status-text {
            font-weight: 800;
            letter-spacing: -0.5px;
            font-size: 1.75rem;
            margin-bottom: 10px;
            color: #fff;
        }

        .sub-text {
            color: rgba(248, 250, 252, 0.6);
            font-weight: 400;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(45, 212, 191, 0.1);
            padding: 8px 16px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--mims-primary);
            margin-top: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 1px solid rgba(45, 212, 191, 0.2);
        }
    </style>
    <meta http-equiv="refresh" content="2.5;url=login.php?msg=logout_success">
</head>
<body>

    <div class="neural-sync"></div>

    <div class="logout-box animate__animated animate__fadeInUp">
        <div class="loader-wrapper">
            <div class="loader"></div>
            <div class="loader-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
        </div>

        <h2 class="status-text">Securing Account</h2>
        <p class="sub-text">Clearing session data and terminating the encrypted link. Returning to the login portal.</p>
        
        <div class="security-badge">
            <i class="bi bi-check2-circle me-2"></i> Session Terminated
        </div>
    </div>

</body>
</html>