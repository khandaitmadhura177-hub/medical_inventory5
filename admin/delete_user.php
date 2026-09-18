<?php
session_start();
include('../config/db_connect.php');

// --- SECURITY: ADMIN ONLY ---
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// --- VALIDATION: CHECK IF ID EXISTS ---
if(isset($_GET['id'])) {
    $delete_id = mysqli_real_escape_string($conn, $_GET['id']);
    $current_admin = $_SESSION['user_id'];

    // Prevent Self-Deletion
    if($delete_id == $current_admin) {
        header("Location: customer_list.php?error=self_delete");
        exit();
    }

    // --- STEP 1: CLEANUP RELATED DATA ---
    // In many systems, you cannot delete a user if they have orders. 
    // We will delete their orders first (or you can choose to keep them by removing this line)
    mysqli_query($conn, "DELETE FROM orders WHERE user_id = '$delete_id'");

    // --- STEP 2: DELETE THE USER ---
    $query = "DELETE FROM users WHERE id = '$delete_id' AND role = 0";
    
    if(mysqli_query($conn, $query)) {
        // Success
        header("Location: customer_list.php?msg=deleted");
    } else {
        // Database Error
        header("Location: customer_list.php?error=db_fail");
    }

} else {
    // No ID provided
    header("Location: customer_list.php");
}
exit();
?>