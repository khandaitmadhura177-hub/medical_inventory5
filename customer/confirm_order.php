<?php
session_start();
include('../config/db_connect.php');

// 0. SECURITY & CANCELLATION LOGIC
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle "Cancel Order" request
if (isset($_GET['action']) && $_GET['action'] == 'cancel') {
    unset($_SESSION['temp_order_details'], $_SESSION['is_direct'], $_SESSION['direct_item']);
    header("Location: ../module/inventory.php?msg=Order Cancelled");
    exit();
}

// STAGE 1: CALCULATION & DISPLAY
if (isset($_POST['customer_name'])) {
    $_SESSION['temp_order_details'] = $_POST;
    
    $subtotal = 0;
    if(isset($_SESSION['is_direct']) && $_SESSION['is_direct'] == true) {
        $subtotal = $_SESSION['direct_item']['price'];
    } elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        // FIX: Added is_array check to prevent Warning on line 26
        foreach($_SESSION['cart'] as $item) { 
            $subtotal += $item['price'] * $item['quantity']; 
        }
    }

    $percent = intval($_POST['discount_type'] ?? 0); 
    $label = "Standard";
    if($percent == 15) $label = "Senior Citizen";
    elseif($percent == 10) $label = "Student/Staff";
    elseif($percent == 5) $label = "New Customer";

    $discount_amount = ($subtotal * $percent) / 100;
    $final_total = $subtotal - $discount_amount; 

    $_SESSION['temp_order_details']['subtotal'] = $subtotal;
    $_SESSION['temp_order_details']['discount_amount'] = $discount_amount;
    $_SESSION['temp_order_details']['discount_label'] = $label; 
    $_SESSION['temp_order_details']['total_amount'] = $final_total;
    
    $payment_mode = $_POST['payment_mode'] ?? 'COD';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Order | MIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { background: #08090a; color: white; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; }
        .confirm-card { background: #111217; border: 1px solid #10b981; border-radius: 30px; padding: 40px; width: 450px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        .price-box { background: rgba(255,255,255,0.03); padding: 25px; border-radius: 20px; margin: 25px 0; text-align: left; border: 1px solid rgba(255,255,255,0.05); }
        .btn-success { background-color: #10b981; border: none; padding: 15px; transition: 0.3s; font-weight: 800; letter-spacing: 1px; }
        .btn-success:hover { background-color: #059669; transform: translateY(-3px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); }
        .action-link { transition: 0.2s; opacity: 0.6; text-decoration: none !important; }
        .action-link:hover { opacity: 1; color: #10b981 !important; }
    </style>
</head>
<body>
    <div class="confirm-card">
        <h4 class="mb-2 fw-800 text-uppercase" style="letter-spacing: 2px;">Review Order</h4>
        <p class="text-muted small mb-4">Final check before your medicines are dispatched.</p>
        
        <div class="price-box">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-white-50 small">Subtotal:</span>
                <span class="small fw-bold text-white">₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            
            <?php if($discount_amount > 0): ?>
            <div class="d-flex justify-content-between text-success mb-2">
                <span class="small fw-bold"><?php echo $label; ?> (<?php echo $percent; ?>%):</span>
                <span class="small fw-bold">- ₹<?php echo number_format($discount_amount, 2); ?></span>
            </div>
            <?php endif; ?>
            
            <hr class="border-secondary opacity-25">
            
            <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold text-white">Grand Total:</span>
                <h3 class="fw-800 mb-0 text-success">₹<?php echo number_format($final_total, 2); ?></h3>
            </div>
        </div>

        <?php if($payment_mode == 'ONLINE'): ?>
            <div class="py-3 mb-3" style="background: rgba(16, 185, 129, 0.1); border-radius: 15px;">
                <p class="small text-success fw-bold mb-0"><i class="bi bi-credit-card me-2"></i>ONLINE PAYMENT SELECTED</p>
                <p class="text-white-50 small mb-0">QR Code will be shown on next step.</p>
            </div>
        <?php else: ?>
            <div class="py-3 mb-3">
                <div class="fs-2 mb-1">🚚</div>
                <p class="text-success fw-800 mb-0">CASH ON DELIVERY</p>
                <p class="text-white-50 small">Pay when you receive your package.</p>
            </div>
        <?php endif; ?>

        <form action="confirm_order.php" method="POST">
            <input type="hidden" name="execute_order" value="1">
            <button type="submit" class="btn btn-success w-100 rounded-pill mb-4">
                CONFIRM & PLACE ORDER
            </button>
        </form>

        <div class="d-flex justify-content-between px-2">
            <a href="javascript:history.back()" class="text-secondary small fw-bold action-link">
                <i class="bi bi-arrow-left me-1"></i> BACK
            </a>

            <a href="confirm_order.php?action=cancel" class="text-danger small fw-bold action-link" 
               onclick="return confirm('Abort this order?')">
                CANCEL <i class="bi bi-x-circle ms-1"></i>
            </a>
        </div>
    </div>
</body>
</html>
<?php exit(); }

// STAGE 2: DATABASE EXECUTION
if (isset($_POST['execute_order'])) {
    if(!isset($_SESSION['temp_order_details'])) {
        header("Location: ../module/inventory.php");
        exit();
    }

    $details = $_SESSION['temp_order_details'];
    $uid = $_SESSION['user_id'];
    $sub = $details['subtotal'];
    $dsc = $details['discount_amount'];
    $lbl = mysqli_real_escape_string($conn, $details['discount_label']); 
    $ttl = $details['total_amount'];
    $pm  = $details['payment_mode'];
    $name = mysqli_real_escape_string($conn, $details['customer_name']);
    $addr = mysqli_real_escape_string($conn, $details['address']);
    $phn  = mysqli_real_escape_string($conn, $details['phone']);

    // 1. Create Main Order Entry
    $q = "INSERT INTO orders (user_id, customer_name, subtotal, discount_amount, discount_label, total_amount, address, phone, status, payment_method, order_date) 
          VALUES ('$uid', '$name', '$sub', '$dsc', '$lbl', '$ttl', '$addr', '$phn', 'Processing', '$pm', NOW())";
    
    if(mysqli_query($conn, $q)) {
        $oid = mysqli_insert_id($conn);
        
        // 2. Transfer Items & REDUCE STOCK
        if(isset($_SESSION['is_direct']) && $_SESSION['is_direct'] == true) {
            $mid = $_SESSION['direct_item']['id'];
            $prc = $_SESSION['direct_item']['price'];
            mysqli_query($conn, "INSERT INTO order_items (order_id, medicine_id, quantity, price_at_purchase) VALUES ('$oid', '$mid', 1, '$prc')");
            mysqli_query($conn, "UPDATE medicines SET quantity = quantity - 1 WHERE id = '$mid'");
        } elseif(isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            // FIX: Added is_array check here as well
            foreach($_SESSION['cart'] as $id => $item) {
                $qty = $item['quantity']; $prc = $item['price'];
                mysqli_query($conn, "INSERT INTO order_items (order_id, medicine_id, quantity, price_at_purchase) VALUES ('$oid', '$id', '$qty', '$prc')");
                mysqli_query($conn, "UPDATE medicines SET quantity = quantity - $qty WHERE id = '$id'");
            }
        }
        
        unset($_SESSION['cart'], $_SESSION['temp_order_details'], $_SESSION['is_direct'], $_SESSION['direct_item']);
        header("Location: order_success.php?id=$oid&mode=$pm");
    } else {
        echo "Database Error: " . mysqli_error($conn);
    }
    exit();
}
?>