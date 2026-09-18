<?php
session_start();
include('../config/db_connect.php');

/**
 * MIMS Profile Synchronization
 * Handles background updates for user account data.
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    
    // 1. Data Sanitization
    $name = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));

    // 2. Validation: Ensure email isn't already taken by another user
    $email_check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != '$uid'");
    if (mysqli_num_rows($email_check) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This email is already registered to another terminal.']);
        exit;
    }

    // 3. Execution: Update profile
    $query = "UPDATE users SET username = '$name', email = '$email' WHERE id = '$uid'";
    
    if (mysqli_query($conn, $query)) {
        // Update session name if you're displaying it on the dashboard
        $_SESSION['user_name'] = $name;
        
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database synchronization failed.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
}
?>