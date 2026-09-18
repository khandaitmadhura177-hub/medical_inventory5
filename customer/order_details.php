<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 1. Fetch the main order info
$query = "SELECT * FROM orders WHERE id = '$order_id' AND user_id = '$user_id'";
$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if(!$order) {
    header("Location: my_orders.php?msg=Order not found.");
    exit();
}

// 2. Fetch specific medicines with a JOIN to get the real medicine name
$items_query = "SELECT oi.*, m.m_name as official_name 
                FROM order_items oi 
                LEFT JOIN medicines m ON oi.medicine_id = m.id 
                WHERE oi.order_id = '$order_id'";
$items_result = mysqli_query($conn, $items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Order #<?php echo $order_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --bg: #030712; 
            --card-bg: #0b0e14; 
            --accent-cyan: #2DD4BF; 
            --border: rgba(45, 212, 191, 0.15);
        }

        body { 
            background-color: var(--bg) !important; 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-image: radial-gradient(circle at top right, rgba(45, 212, 191, 0.05) 0%, transparent 40%);
            min-height: 100vh;
        }

        .details-panel { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 30px; 
            padding: 40px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .info-label { color: var(--accent-cyan); font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
        .info-value { font-size: 1.1rem; font-weight: 600; margin-bottom: 20px; }

        .medicine-row { 
            background: rgba(255,255,255,0.02); 
            border-radius: 18px; 
            padding: 20px; 
            margin-bottom: 12px; 
            border: 1px solid var(--border); 
            transition: 0.3s ease;
        }
        .medicine-row:hover { background: rgba(45, 212, 191, 0.05); transform: scale(1.01); }

        .back-btn { 
            color: rgba(255,255,255,0.5); 
            text-decoration: none; 
            font-weight: 700; 
            display: inline-flex; 
            align-items: center; 
            gap: 10px; 
            transition: 0.3s;
        }
        .back-btn:hover { color: var(--accent-cyan); transform: translateX(-5px); }
        
        .badge-status {
            background: rgba(251, 191, 36, 0.1);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.2);
            padding: 5px 15px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 800;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <a href="my_orders.php" class="back-btn mb-4">
                <i class="bi bi-arrow-left-circle-fill fs-4"></i>
                <span class="text-uppercase small fw-800">Return to Logs</span>
            </a>

            <div class="details-panel shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="fw-900 m-0" style="font-family: 'Outfit'; letter-spacing: -1px;">Order <span style="color: var(--accent-cyan);">Specification</span></h2>
                        <p class="text-white-50 small mono mt-1">REF: #ORD-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div class="text-end">
                        <div class="info-label">Total Paid</div>
                        <div class="h3 fw-900 text-white">₹<?php echo number_format($order['total_amount'], 2); ?></div>
                    </div>
                </div>

                <h5 class="fw-800 mb-4 text-uppercase small text-cyan" style="letter-spacing: 2px;">Prescription / Medicine Breakdown</h5>
                
                <?php if(mysqli_num_rows($items_result) > 0): ?>
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="medicine-row d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="bg-dark rounded-3 p-2 me-3 border border-secondary">
                                    <i class="bi bi-capsule-pill text-info fs-4"></i>
                                </div>
                                <div>
                                    <span class="fw-bold d-block"><?php echo htmlspecialchars($item['official_name'] ?? $item['medicine_name'] ?? 'Medical Item'); ?></span>
                                    <div class="text-white-50 small">Quantity: <?php echo $item['quantity']; ?> Unit(s)</div>
                                </div>
                            </div>
                            <div class="fw-bold text-white">
                                ₹<?php 
                                    $price = $item['price'] ?? $item['unit_price'] ?? $item['price_at_purchase'] ?? 0; 
                                    echo number_format($price * $item['quantity'], 2); 
                                ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4 opacity-50">
                        <i class="bi bi-folder-x fs-2"></i>
                        <p class="small mt-2">No detailed item logs found.</p>
                    </div>
                <?php endif; ?>

                <hr style="border-color: var(--border); margin: 30px 0;">

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Transaction Status</div>
                        <div class="mt-1"><span class="badge-status"><?php echo strtoupper($order['status'] ?? 'PENDING'); ?></span></div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="info-label">Timestamp</div>
                        <div class="info-value mb-0"><?php echo date('M d, Y | H:i', strtotime($order['order_date'] ?? $order['created_at'])); ?></div>
                    </div>
                </div>

                <div class="mt-5">
                    <a href="invoice.php?id=<?php echo $order_id; ?>" class="btn btn-info w-100 rounded-pill py-3 fw-800 shadow-sm" style="background: var(--accent-cyan); border: none; color: #000;">
                        <i class="bi bi-printer-fill me-2"></i>GENERATE PDF INVOICE
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>