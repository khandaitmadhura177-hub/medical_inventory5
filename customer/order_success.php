<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Ensure user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = $_GET['id'] ?? '';
$mode = $_GET['mode'] ?? 'COD';
$total_to_pay = "0.00";

// 2. DATA FETCH: Get order details to confirm existence
if(!empty($order_id)) {
    $user_id = $_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT total_amount FROM orders WHERE id = '$order_id' AND user_id = '$user_id'");
    if($row = mysqli_fetch_assoc($res)) {
        $total_to_pay = $row['total_amount'];
    } else {
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Order Acknowledgement</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --cyan: #2DD4BF; --bg: #030712; }
        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.05) 0%, transparent 70%);
        }
        .swal2-popup {
            border-radius: 30px !important;
            background: #ffffff !important;
            padding: 2rem !important;
        }
        .swal2-title { font-weight: 800 !important; color: #0f172a !important; }
    </style>
</head>
<body>

<script>
    const orderId = "<?php echo $order_id; ?>";
    const paymentMode = "<?php echo $mode; ?>";
    const totalPayable = "<?php echo number_format($total_to_pay, 2); ?>";

    if (paymentMode !== 'COD') {
        Swal.fire({
            title: 'Secure Payment Terminal',
            html: `
                <div class="p-2">
                    <p class="mb-3" style="font-size: 1.1rem; color: #475569;">Payable: <span style="color: #10b981; font-weight:800;">₹${totalPayable}</span></p>
                    <div style="background: #f8fafc; padding: 20px; border-radius: 20px; display: inline-block; border: 1px solid #e2e8f0;">
                        <img src="../assets/image/medicine/myqrcode.jpeg" 
                             onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=MIMS_ORDER_${orderId}'"
                             style="width:200px; border-radius: 10px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
                    </div>
                    <p class="mt-4" style="font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                        Scan QR with GPay, PhonePe, or any UPI app.<br>
                        <span style="font-weight: 700; color: #0f172a; font-size: 1rem;">ORDER REFERENCE: #ORD-${orderId}</span>
                    </p>
                </div>
            `,
            confirmButtonText: 'I HAVE COMPLETED PAYMENT',
            confirmButtonColor: '#10b981',
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Verifying Transaction...',
                    html: 'Connecting to MIMS banking node',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });

                fetch('auto_verify.php?id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'verified' || data.trim() === 'already_processed') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Confirmed',
                            text: 'Order placed successfully.',
                            showConfirmButton: false,
                            timer: 2000
                        }).then(() => {
                            window.location.href = 'invoice.php?id=' + orderId;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Verification Failed',
                            text: data
                        });
                    }
                });
            }
        });
    } else {
        Swal.fire({
            icon: 'success',
            title: 'Order Placed!',
            text: 'Mode: Cash on Delivery. Generating receipt...',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        }).then(() => {
            window.location.href = 'invoice.php?id=' + orderId;
        });
    }
</script>
</body>
</html>