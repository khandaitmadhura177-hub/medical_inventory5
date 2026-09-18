<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) { exit("unauthorized"); }
$user_id = $_SESSION['user_id'];

if(isset($_GET['id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Check if the order exists, belongs to THIS user, and is 'Pending'
    $check_query = "SELECT status FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
    $check_res = mysqli_query($conn, $check_query);
    
    if($order = mysqli_fetch_assoc($check_res)) {
        // Case-insensitive check just in case
        if(strtolower($order['status']) == 'pending') {
            
            $query = "UPDATE orders SET status = 'Processing' WHERE id = '$order_id'";
            
            if(mysqli_query($conn, $query)) {
                echo "verified";
            } else {
                echo "error_updating";
            }
            
        } else {
            echo "already_processed";
        }
    } else {
        echo "not_found";
    }
}
?>