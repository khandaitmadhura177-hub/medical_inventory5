<?php
session_start();
include('../config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Validate Cart is not empty before proceeding
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        header("Location: cart.php?error=empty_session");
        exit();
    }

    // 2. Capture All Data into Temporary Session
    // We store this so we can insert it into the DB AFTER the payment is confirmed
    $_SESSION['temp_order'] = [
        'name'     => mysqli_real_escape_string($conn, $_POST['customer_name']),
        'email'    => mysqli_real_escape_string($conn, $_POST['email']),
        'phone'    => mysqli_real_escape_string($conn, $_POST['phone']),
        'address'  => mysqli_real_escape_string($conn, $_POST['address']),
        'pincode'  => mysqli_real_escape_string($conn, $_POST['pincode'] ?? ''),
        'category' => mysqli_real_escape_string($conn, $_POST['claimed_category'] ?? 'General'),
        'method'   => $_POST['payment_mode'], // 'ONLINE' or 'COD'
        'total'    => $_POST['final_amount']
    ];

    // 3. The Branching Logic
    if ($_POST['payment_mode'] == 'ONLINE') {
        // Redirect to the QR/UPI screen we built earlier
        header("Location: order_success.php?mode=ONLINE&temp=true");
    } else {
        // Redirect directly to the order placement logic for COD
        header("Location: place_order_logic.php?mode=COD");
    }
    exit();
} else {
    header("Location: checkout.php");
    exit();
}