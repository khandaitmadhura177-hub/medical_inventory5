<?php
session_start();
include('../config/db_connect.php');
header('Content-Type: application/json');

// 1. Security Check: Block guests
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// 2. Process POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Validate Message Content
    $raw_msg = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if (empty($raw_msg)) {
        echo json_encode(['status' => 'error', 'message' => 'Message buffer empty']);
        exit();
    }
    
    // Sanitize for Database Safety
    $msg = mysqli_real_escape_string($conn, $raw_msg);

    /**
     * Note: Ensure your table 'support_messages' has the following:
     * id (INT, AI, PK)
     * user_id (INT)
     * message (TEXT)
     * status (VARCHAR) - Default 'pending'
     * admin_reply (TEXT) - Default NULL
     * created_at (DATETIME)
     */
    $sql = "INSERT INTO support_messages (user_id, message, status, created_at) 
            VALUES ('$user_id', '$msg', 'pending', NOW())";
            
    if (mysqli_query($conn, $sql)) {
        // Return success so the JavaScript can clear the input field
        echo json_encode(['status' => 'success']);
    } else {
        // Log error for the developer but keep it vague for the user
        echo json_encode(['status' => 'error', 'message' => 'Transmission failed']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Protocol']);
}