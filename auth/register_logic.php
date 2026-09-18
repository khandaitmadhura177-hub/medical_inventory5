<?php
session_start();
include('../config/db_connect.php');

if (isset($_POST['register'])) {
    // 1. Collect and sanitize input
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); 
    
    // --- THEMED TRANSITION UI: HARMONIZED WITH MIMS CORE ---
    echo "
    <style>
        body { 
            background: #0F172A; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin:0; 
            color: #F8FAFC; 
        }
        .loader-box { 
            text-align: center; 
            border: 1px solid rgba(45, 212, 191, 0.1); 
            padding: 50px; 
            border-radius: 35px; 
            background: #1E293B; 
            backdrop-filter: blur(20px); 
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
        }
        .spinner { 
            border: 4px solid rgba(45, 212, 191, 0.1); 
            border-left-color: #2DD4BF; 
            border-radius: 50%; 
            width: 50px; 
            height: 50px; 
            animation: spin 0.8s linear infinite; 
            margin: 0 auto 25px; 
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .status { 
            color: #2DD4BF; 
            font-weight: 800; 
            letter-spacing: 2px; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            filter: drop-shadow(0 0 8px rgba(45, 212, 191, 0.4));
        }
    </style>
    <div class='loader-box'>
        <div class='spinner'></div>
        <div class='status'>Initializing Identity Protocol...</div>
    </div>";

    // 2. Check if user or email already exists
    $check_user = "SELECT id FROM users WHERE username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($conn, $check_user);

    if (mysqli_num_rows($result) > 0) {
        header("Location: register.php?error=user_exists");
        exit();
    } else {
        // 3. Insert new user (Role 0 = Customer)
        $query = "INSERT INTO users (full_name, username, email, password, role) 
                  VALUES ('$fullname', '$username', '$email', '$password', 0)";
        
        if (mysqli_query($conn, $query)) {
            // Success! Redirect to login with success message
            header("Location: login.php?success=account_created");
            exit();
        } else {
            // Database Error
            header("Location: register.php?error=registration_failed");
            exit();
        }
    }
} else {
    header("Location: register.php");
    exit();
}
?>