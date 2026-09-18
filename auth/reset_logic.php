<?php
session_start();
include('../config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // --- THEMED PROCESSING UI (KEEPING YOUR EXACT STYLE) ---
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700&display=swap');
            body { 
                background: #0a0a0a; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                height: 100vh; 
                font-family: 'Plus Jakarta Sans', sans-serif; 
                margin:0; 
                color: #fff; 
            }
            .loader-box { 
                text-align: center; 
                border: 1px solid rgba(255, 179, 71, 0.1); 
                padding: 60px; 
                border-radius: 40px; 
                background: rgba(20, 20, 20, 0.9); 
                backdrop-filter: blur(20px);
                box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            }
            .spinner { 
                border: 3px solid rgba(255, 179, 71, 0.1); 
                border-left-color: #ffb347; 
                border-radius: 50%; 
                width: 50px; 
                height: 50px; 
                animation: spin 0.8s linear infinite; 
                margin: 0 auto 25px; 
            }
            @keyframes spin { to { transform: rotate(360deg); } }
            .status { 
                color: #ffb347; 
                font-weight: 700; 
                letter-spacing: 3px; 
                text-transform: uppercase; 
                font-size: 0.75rem; 
            }
        </style>
    </head>
    <body>
        <div class='loader-box'>
            <div class='spinner'></div>
            <div class='status'>Verifying Identity...</div>
        </div>
    </body>
    </html>";

    // 1. Verify if the email exists
    $query = "SELECT username FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // --- LOGIC CHANGE HERE ---
        // We set the session so reset_password.php knows who is being updated
        $_SESSION['reset_email'] = $email;

        // Simulate processing delay
        usleep(800000); 
        
        // Redirect to the reset password terminal instead of login
        header("Location: reset_password.php");
        exit();
    } else {
        // 3. Error Logic
        header("Location: forget_password.php?error=not_found");
        exit();
    }
} else {
    header("Location: forget_password.php");
    exit();
}
?>