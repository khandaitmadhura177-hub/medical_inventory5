<?php
session_start();
include('../config/db_connect.php');

if (isset($_GET['id'])) {
    $medicine_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = isset($_GET['action']) ? $_GET['action'] : 'add';
    
    // Fetch medicine details and current stock
    $query = "SELECT * FROM medicines WHERE id = '$medicine_id'";
    $result = mysqli_query($conn, $query);
    $medicine = mysqli_fetch_assoc($result);

    if ($medicine) {
        // Initialize cart
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Current stock available in DB
        $available_stock = $medicine['quantity'];
        
        // Check current quantity in cart
        $current_cart_qty = isset($_SESSION['cart'][$medicine_id]) ? $_SESSION['cart'][$medicine_id]['quantity'] : 0;

        // --- VALIDATION: Check against stock ---
        if ($current_cart_qty + 1 > $available_stock) {
            // Not enough stock
            header("Location: ../module/inventory.php?status=out_of_stock&msg=" . urlencode($medicine['m_name'] . " has limited stock."));
            exit();
        }

        // Increase quantity or add new item
        if (isset($_SESSION['cart'][$medicine_id])) {
            $_SESSION['cart'][$medicine_id]['quantity'] += 1;
        } else {
            $_SESSION['cart'][$medicine_id] = array(
                "name" => $medicine['m_name'],
                "price" => $medicine['price'],
                "quantity" => 1,
                "image" => $medicine['image']
            );
        }
        
        // --- REDIRECT LOGIC ---
        if ($action == 'buy') {
            header("Location: cart.php");
            exit();
        }

        header("Location: ../module/inventory.php?status=added");
        exit();
    }
} else {
    header("Location: ../module/inventory.php");
    exit();
}