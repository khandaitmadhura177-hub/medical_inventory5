<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Authentication Guard
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply_text'])) {
    // Sanitization for secure transmission
    $msg_id = mysqli_real_escape_string($conn, $_POST['msg_id']);
    $reply = mysqli_real_escape_string($conn, $_POST['reply_text']);

    // 2. DATABASE UPDATE: Set the reply and mark status as Resolved
    // We update the specific message and toggle status to 'Resolved'
    $query = "UPDATE contact_messages 
              SET admin_reply = '$reply', 
                  status = 'Resolved' 
              WHERE id = '$msg_id'";
    
    if(mysqli_query($conn, $query)) {
        // 3. SUCCESS UI: Dark Rose Gold Terminal Style Redirect
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="refresh" content="2;url=message.php?success=1">
            <title>MIMS | Transmission Confirmed</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
                
                :root {
                    --mims-bg: #0b0c10;
                    --rose-gold: #c5a1a1;
                    --card-bg: #111216;
                    --glass-border: rgba(197, 161, 161, 0.2);
                }

                body { 
                    background-color: var(--mims-bg); 
                    height: 100vh; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-family: 'Plus Jakarta Sans', sans-serif; 
                    color: #ffffff;
                    margin: 0;
                    overflow: hidden;
                    background-image: radial-gradient(circle at center, rgba(197, 161, 161, 0.05) 0%, transparent 70%);
                }

                .success-card { 
                    background: var(--card-bg); 
                    padding: 60px 40px; 
                    border-radius: 40px; 
                    text-align: center; 
                    border: 1px solid var(--glass-border);
                    box-shadow: 0 40px 100px rgba(0,0,0,0.6);
                    max-width: 450px;
                    width: 90%;
                    position: relative;
                }

                .icon-box { 
                    width: 80px; 
                    height: 80px; 
                    background: rgba(16, 185, 129, 0.05); 
                    color: #10b981; 
                    border: 1px solid rgba(16, 185, 129, 0.2);
                    border-radius: 24px; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    margin: 0 auto 30px; 
                    font-size: 2.5rem;
                }

                .status-line {
                    height: 1px;
                    background: rgba(255,255,255,0.05);
                    width: 100%;
                    margin: 25px 0;
                    position: relative;
                }

                .status-line::after {
                    content: '';
                    position: absolute;
                    left: 0;
                    width: 30%;
                    height: 100%;
                    background: var(--rose-gold);
                    box-shadow: 0 0 10px var(--rose-gold);
                    animation: sweep 1.5s ease-in-out infinite;
                }

                @keyframes sweep {
                    0% { left: 0%; width: 0%; }
                    50% { left: 0%; width: 100%; }
                    100% { left: 100%; width: 0%; }
                }

                .terminal-text {
                    font-family: 'JetBrains Mono', monospace;
                    font-size: 0.7rem;
                    letter-spacing: 2px;
                    text-transform: uppercase;
                    color: var(--rose-gold);
                    opacity: 0.8;
                }
            </style>
        </head>
        <body>
            <div class="success-card animate__animated animate__zoomIn">
                <div class="icon-box animate__animated animate__check">
                    <i class="bi bi-send-check"></i>
                </div>
                <h3 class="fw-800 mb-2" style="letter-spacing: -1px;">Response Transmitted</h3>
                <p class="text-white-50 small mb-0 px-3">
                    Identity interaction recorded. Database pointer updated to <span class="text-success fw-bold">RESOLVED</span>.
                </p>
                
                <div class="status-line"></div>
                <div class="terminal-text animate__animated animate__pulse animate__infinite">Syncing Command Terminal...</div>
            </div>
        </body>
        </html>
        <?php
    } else {
        die("<div style='color: #ff4b5c; font-family: monospace; padding: 20px;'>SQL_FAILURE: " . mysqli_error($conn) . "</div>");
    }
} else {
    header("Location: message.php");
}
?>