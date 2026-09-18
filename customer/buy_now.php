<?php
session_start();
include('../config/db_connect.php');

/**
 * MIMS EXPRESS CHECKOUT
 * Clears existing session cart to focus exclusively on the 'Buy Now' selection.
 */

if(isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // 1. Fetch details and verify stock is actually available (> 0)
    $query = "SELECT * FROM medicines WHERE id = '$id' AND quantity > 0 LIMIT 1";
    $result = mysqli_query($conn, $query);

    if($row = mysqli_fetch_assoc($result)) {
        
        // 2. EXPRESS LOGIC: Clear existing cart to prevent mixed-order confusion
        // If you want to keep old items instead, simply comment out the line below.
        $_SESSION['cart'] = []; 

        // 3. Register the single 'Buy Now' item
        $_SESSION['cart'][$id] = [
            'name'     => $row['m_name'],
            'price'    => $row['price'],
            'quantity' => 1,
            'image'    => $row['image'] // Added for UI consistency on checkout
        ];

        // 4. Immediate redirection to Billing/Checkout
        header("Location: checkout.php");
        exit();
    } else {
        // Handle Out of Stock scenario
        header("Location: ../module/inventory.php?status=out_of_stock");
        exit();
    }
}

// Global Fallback
header("Location: index.php");
exit();
?>