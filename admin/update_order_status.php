<?php
session_start();
include('../config/db_connect.php');

/**
 * MIMS ORDER TERMINAL LOGIC
 * Handles Approval (with stock deduction) and Rejection (price reset).
 */

// 1. SECURITY: Admin Authentication Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

if(isset($_GET['id']) && isset($_GET['action'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if($action == 'approve') {
        // --- STEP A: Fetch order details for stock deduction ---
        $order_check = mysqli_query($conn, "SELECT medicine_id, quantity FROM orders WHERE id = $id AND status = 'Pending'");
        
        if($order_data = mysqli_fetch_assoc($order_check)) {
            $m_id = $order_data['medicine_id'];
            $qty_to_deduct = $order_data['quantity'];

            // --- STEP B: Update stock levels ---
            // Using a single query to decrement quantity
            $stock_update = "UPDATE medicines SET quantity = quantity - $qty_to_deduct WHERE id = $m_id";
            mysqli_query($conn, $stock_update);

            // --- STEP C: Finalize Order Status ---
            $sql = "UPDATE orders SET 
                    status = 'Approved', 
                    discount_status = 'approved' 
                    WHERE id = $id";
        } else {
            // Already processed or doesn't exist
            header("Location: order.php?status=info&msg=Order already processed");
            exit();
        }
                
    } elseif($action == 'reject') {
        // --- REJECTION LOGIC ---
        // Resets price and logs as rejected
        $sql = "UPDATE orders SET 
                status = 'Rejected', 
                discount_status = 'rejected', 
                total_amount = subtotal, 
                discount_amount = 0 
                WHERE id = $id";
    } else {
        header("Location: order.php?error=Invalid Action");
        exit();
    }

    // 2. EXECUTION & REDIRECT
    if(mysqli_query($conn, $sql)) {
        header("Location: order.php?status=success&msg=Order ID #$id Processed Successfully");
        exit();
    } else {
        die("CRITICAL SYSTEM ERROR: " . mysqli_error($conn));
    }
} else {
    header("Location: order.php");
    exit();
}
?>