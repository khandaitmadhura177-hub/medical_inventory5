<?php
session_start();
include('../config/db_connect.php');

/**
 * MIMS Return Request Logic
 * Background Processor - No UI required.
 */

// 1. SECURITY: Validate session and ID
if(!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['user_id'];
$reason = mysqli_real_escape_string($conn, $_GET['reason'] ?? 'No reason provided');

// 2. VALIDATION: Ensure ownership and correct state
$check_query = "SELECT status FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$check_result = mysqli_query($conn, $check_query);

if(mysqli_num_rows($check_result) == 1) {
    $order = mysqli_fetch_assoc($check_result);
    $current_status = strtolower($order['status']);

    // Blocked if already returning, returned, or cancelled
    $blocked_statuses = ['cancelled', 'return requested', 'returned'];
    
    if(in_array($current_status, $blocked_statuses)) {
        header("Location: dashboard.php?msg=Order state prevents further return requests.");
        exit();
    }

    // 3. EXECUTION: Update status and log the reason if you have a 'return_reason' column
    // Note: I am assuming your 'orders' table has a 'status' column.
    $query = "UPDATE orders SET status = 'Return Requested' WHERE id = '$order_id' AND user_id = '$user_id'";

    if(mysqli_query($conn, $query)) {
        // Option: Log to support_messages so Admin sees the reason in the Communications Hub
        $log_msg = "AUTO-GEN: Return requested for Order #$order_id. Reason: $reason";
        mysqli_query($conn, "INSERT INTO support_messages (user_id, order_id, message, created_at) 
                            VALUES ('$user_id', '$order_id', '$log_msg', NOW())");

        header("Location: dashboard.php?msg=Return protocol initiated. Pharmacist notified.");
        exit();
    } else {
        header("Location: dashboard.php?msg=Database synchronization failed.");
        exit();
    }
} else {
    header("Location: dashboard.php?msg=Unauthorized protocol access.");
    exit();
}
?>