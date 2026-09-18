<?php
session_start();
include('../config/db_connect.php');

// UI Wrapper for the processing state
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Neural Processing</title>
    <style>
        body {
            background: #030712;
            color: #2DD4BF;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        .loader-container { text-align: center; }
        .scanner {
            width: 300px;
            height: 2px;
            background: #2DD4BF;
            box-shadow: 0 0 15px #2DD4BF;
            position: relative;
            animation: scan 2s infinite;
        }
        .text {
            margin-top: 20px;
            letter-spacing: 2px;
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: bold;
            opacity: 0.8;
        }
        @keyframes scan {
            0% { transform: translateY(-20px); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(20px); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="scanner"></div>
        <div class="text">Synchronizing Data...</div>
    </div>

<?php
if (isset($_POST['confirm_order'])) {
    $u_id = $_SESSION['user_id'];
    
    // Capture form data
    $customer_name = mysqli_real_escape_string($conn, $_POST['customer_name'] ?? '');
    $phone         = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
    $address       = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
    $pincode       = mysqli_real_escape_string($conn, $_POST['pincode'] ?? ''); 
    $payment_mode  = mysqli_real_escape_string($conn, $_POST['payment_mode'] ?? 'COD');
    $claimed_cat   = mysqli_real_escape_string($conn, $_POST['claimed_category'] ?? 'General');
    
    $status = 'Pending'; 
    $date = date('Y-m-d H:i:s');

    // 1. Calculate the Subtotal
    $subtotal = 0;
    foreach($_SESSION['cart'] as $item) {
        $subtotal += ($item['price'] * $item['quantity']);
    }

    // 2. Set Discount Logic
    $discount_amount = 0;
    if ($claimed_cat == 'Senior Citizen' || $claimed_cat == 'Physically Handicapped') {
        $discount_amount = $subtotal * 0.20; 
    } elseif ($claimed_cat == 'Health Card') {
        $discount_amount = $subtotal * 0.10; 
    }

    $final_total = $subtotal - $discount_amount;

    // Start Transaction
    mysqli_begin_transaction($conn);

    try {
        // 4. INSERT MAIN ORDER
        $sql = "INSERT INTO orders (user_id, customer_name, subtotal, discount_amount, total_amount, phone, address, pincode, order_date, status, payment_mode, claimed_category, discount_status) 
                VALUES ('$u_id', '$customer_name', '$subtotal', '$discount_amount', '$final_total', '$phone', '$address', '$pincode', '$date', '$status', '$payment_mode', '$claimed_cat', 'pending')";

        if (!mysqli_query($conn, $sql)) throw new Exception("Order Header Failure");
        
        $order_id = mysqli_insert_id($conn);
        
        foreach($_SESSION['cart'] as $m_id => $item) {
            $m_id_clean = mysqli_real_escape_string($conn, $m_id);
            $qty = (int)$item['quantity'];
            $price = $item['price'];

            // INSERT ITEMS
            mysqli_query($conn, "INSERT INTO order_items (order_id, medicine_id, quantity, price_at_purchase) 
                                VALUES ('$order_id', '$m_id_clean', '$qty', '$price')");

            // REDUCE STOCK
            mysqli_query($conn, "UPDATE medicines SET quantity = quantity - $qty WHERE id = '$m_id_clean'");
        }

        mysqli_commit($conn);
        unset($_SESSION['cart']);
        
        // Short JS delay so user sees the cool animation
        echo "<script>
                setTimeout(function(){
                    window.location.href = 'order_success.php?id=$order_id&mode=" . urlencode($payment_mode) . "';
                }, 1200);
              </script>";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<div style='color:red; text-align:center;'>System Fault: " . $e->getMessage() . "</div>";
    }
}
?>
</body>
</html>