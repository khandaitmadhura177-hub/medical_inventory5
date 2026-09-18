<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- STEP 1: DYNAMIC PATH DETECTION ---
$project_root = dirname(__DIR__); 
$possible_paths = [
    $project_root . '/PHPMailer-master/src/',
    $project_root . '/PHPMailer-master/PHPMailer-master/src/',
    $project_root . '/PHPMailer/src/' // Added standard folder name just in case
];

$base_dir = null;
foreach ($possible_paths as $path) {
    if (file_exists($path . 'PHPMailer.php')) {
        $base_dir = $path;
        break;
    }
}

if (!$base_dir) {
    die("Fatal Error: Could not find PHPMailer files. Please check your folder structure.");
}

require $base_dir . 'Exception.php';
require $base_dir . 'PHPMailer.php';
require $base_dir . 'SMTP.php';

include('../config/db_connect.php');

if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Changed to check for both 'id' and 'order_id' to match the button click
if(isset($_GET['id']) || isset($_GET['order_id'])) {
    $order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : mysqli_real_escape_string($conn, $_GET['order_id']);
    
    // Updated Query: Using 'payment_method' based on your invoice code logic
    $query = "SELECT o.*, u.email as account_email FROM orders o 
              LEFT JOIN users u ON o.user_id = u.id WHERE o.id = '$order_id'";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);

    if(!$order) { die("Order not found."); }

    $mail = new PHPMailer(true);

    try {
        // --- STEP 2: SMTP SERVER SETTINGS ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'khandaitmadhura177@gmail.com'; 
        $mail->Password   = 'skqjihmotiakqeet'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- STEP 3: RECIPIENTS ---
        $mail->setFrom('khandaitmadhura177@gmail.com', 'MIMS Health');
        $mail->addAddress($order['account_email'], $order['customer_name']);

        // --- STEP 4: EMAIL CONTENT ---
        $mail->isHTML(true);
        $mail->Subject = "Digital Invoice Confirmation - #ORD-" . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        // Match the payment logic from your invoice display
        $p_method = $order['payment_method'] ?? 'COD';

        // Styling the email to match your MIMS theme better
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; border: 1px solid #2DD4BF; padding: 30px; max-width: 600px; margin: auto; background-color: #0b0e14; color: #ffffff;'>
                <h2 style='color: #2DD4BF; text-align: center; margin-bottom: 5px;'>MIMS.HEALTH</h2>
                <p style='text-align: center; color: #888; font-size: 12px; margin-top: 0;'>Secure Pharmaceutical Logistics</p>
                
                <hr style='border: 0; border-top: 1px solid #222; margin: 20px 0;'>
                
                <p>Hello <b>" . htmlspecialchars($order['customer_name']) . "</b>,</p>
                <p>Your order has been verified successfully. Your digital receipt summary is below:</p>
                
                <div style='background: #030712; padding: 20px; border-radius: 10px; border: 1px solid #1e293b;'>
                    <p style='margin: 5px 0;'><b>Order ID:</b> #ORD-" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . "</p>
                    <p style='margin: 5px 0;'><b>Date:</b> " . date('F d, Y', strtotime($order['order_date'])) . "</p>
                    <p style='margin: 5px 0;'><b>Payment Method:</b> " . $p_method . "</p>
                    <p style='margin: 15px 0 5px 0; font-size: 18px;'><b>Total Amount:</b> <span style='color: #2DD4BF;'>₹" . number_format($order['total_amount'], 2) . "</span></p>
                </div>
                
                <p style='margin-top: 25px; color: #2DD4BF; font-weight: bold;'>Delivery Address:</p>
                <p style='color: #ccc; font-style: italic; background: #161b22; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($order['address']) . "</p>
                
                <hr style='border: 0; border-top: 1px solid #222; margin: 25px 0;'>
                <p style='font-size: 11px; color: #666; text-align: center;'>This is an automated transmission from MIMS Node #" . str_pad($order['user_id'], 4, '0', STR_PAD_LEFT) . ".<br>Thank you for choosing MIMS Pharmacy.</p>
            </div>";

        $mail->send();
        
        // Redirect back with 'status=sent' to trigger the SweetAlert in invoice.php
        header("Location: invoice.php?id=" . $order_id . "&status=sent");
        exit();

    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
} else {
    echo "No Order ID provided.";
}
?>