<?php
session_start();
include('../config/db_connect.php');

// 1. Security Check
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if ($order_id && $action) {
    // 2. Verify Ownership & Check Status
    $check_sql = "SELECT id, status FROM orders WHERE id = '$order_id' AND user_id = '$user_id' LIMIT 1";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $order = mysqli_fetch_assoc($check_result);
        $current_status = strtolower($order['status']);

        // 3. Logic Gate: Updated to allow 'delivered' status specifically for returns
        if (($action == 'cancel' && $current_status != 'delivered' && $current_status != 'completed' && $current_status != 'cancelled') || 
            ($action == 'return' && $current_status == 'delivered')) {
            
            if ($action == 'cancel') {
                // START TRANSACTION: Ensure both status update and restocking happen together
                mysqli_begin_transaction($conn);

                try {
                    // Update Order Status
                    mysqli_query($conn, "UPDATE orders SET status = 'CANCELLED' WHERE id = '$order_id'");

                    // RESTOCK MEDICINES: Loop through items in this order and add quantities back to medicine table
                    $items_res = mysqli_query($conn, "SELECT medicine_id, quantity FROM order_items WHERE order_id = '$order_id'");
                    while($item = mysqli_fetch_assoc($items_res)) {
                        $m_id = $item['medicine_id'];
                        $qty = $item['quantity'];
                        mysqli_query($conn, "UPDATE medicines SET quantity = quantity + $qty WHERE id = '$m_id'");
                    }

                    mysqli_commit($conn);
                    $redirect_msg = "cancelled";
                    // Redirect to new confirmation page
                    header("Location: order_status_action.php?id=$order_id&type=cancel");
                    exit();
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $redirect_msg = "error";
                }
            } 
            elseif ($action == 'return') {
                // Updated status to 'RETURNED' to match your admin/returns_report.php expectations
                mysqli_query($conn, "UPDATE orders SET status = 'RETURNED' WHERE id = '$order_id'");
                $redirect_msg = "return_pending";
                // Redirect to new confirmation page
                header("Location: order_status_action.php?id=$order_id&type=return");
                exit();
            }
            
            header("Location: dashboard.php?msg=$redirect_msg");
            exit();
        }
    }
}

// Redirect if invalid request or security fail
header("Location: dashboard.php?msg=denied");
exit();
?>