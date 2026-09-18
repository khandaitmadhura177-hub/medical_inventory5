<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

if(isset($_GET['id']) || isset($_GET['order_id'])) {
    $order_id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : mysqli_real_escape_string($conn, $_GET['order_id']);
    $user_id = $_SESSION['user_id'];
    
    $check_clause = ($_SESSION['role'] == 1) ? "" : "AND o.user_id = '$user_id'";
    $query = "SELECT o.*, u.username as account_username, u.email as account_email 
              FROM orders o LEFT JOIN users u ON o.user_id = u.id
              WHERE o.id = '$order_id' $check_clause";
              
    $result = mysqli_query($conn, $query);
    
    if(mysqli_num_rows($result) == 1) {
        $order = mysqli_fetch_assoc($result);
        $items_query = "SELECT oi.*, m.m_name, (oi.price_at_purchase * oi.quantity) as item_total 
                        FROM order_items oi JOIN medicines m ON oi.medicine_id = m.id 
                        WHERE oi.order_id = '$order_id'";
        $items_result = mysqli_query($conn, $items_query);
        $grand_total = $order['total_amount'];
        $original_subtotal = $order['subtotal'] ?? $grand_total; 
        $discount_value = $order['discount_amount'] ?? 0;
    } else { die("Order not found or access denied."); }
} else { header("Location: dashboard.php"); exit(); }

$back_to = ($_SESSION['role'] == 1) ? "../admin/order.php" : "dashboard.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #ORD-<?php echo $order['id']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Outfit:wght@700;800&family=JetBrains+Mono&display=swap');

        :root {
            --bg: #030712;
            --panel: #0b0e14;
            --cyan: #2DD4BF;
            --text: #F8FAFC;
            --border: rgba(45, 212, 191, 0.15);
            --danger: #ef4444;
        }

        body { background: var(--bg); font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text); padding: 40px 0; }
        
        .bill-container { 
            background: var(--panel); 
            max-width: 850px; 
            margin: auto; 
            padding: 60px; 
            border: 1px solid var(--border);
            border-radius: 25px;
            position: relative;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .status-stamp {
            position: absolute; top: 120px; right: 50px;
            border: 4px double; font-weight: 800; padding: 10px 20px;
            transform: rotate(-15deg); opacity: 0.2; text-transform: uppercase;
            border-radius: 8px; font-size: 1.8rem; pointer-events: none; z-index: 1;
        }
        .stamp-paid { border-color: var(--cyan); color: var(--cyan); }
        .stamp-cod { border-color: #fbbf24; color: #fbbf24; }

        .table { color: var(--text); border-color: var(--border); }
        .table thead { background: rgba(45, 212, 191, 0.1); color: var(--cyan); }
        .table td { border-color: var(--border); padding: 15px; }

        .total-box { 
            background: rgba(45, 212, 191, 0.05); 
            padding: 25px; 
            border-radius: 15px;
            border: 1px solid var(--border);
        }

        .btn-cyan { background: var(--cyan); color: #030712; font-weight: 800; border: none; border-radius: 50px; }
        .btn-outline-cyan { border: 1px solid var(--cyan); color: var(--cyan); font-weight: 600; border-radius: 50px; }
        .btn-outline-cyan:hover { background: var(--cyan); color: #030712; }
        
        /* Logout styling */
        .btn-logout { border: 1px solid var(--danger); color: var(--danger); border-radius: 50px; font-weight: 600; }
        .btn-logout:hover { background: var(--danger); color: white; }

        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; padding: 0; }
            .bill-container { 
                background: white; border: none; box-shadow: none; 
                width: 100%; max-width: 100%; padding: 20px; color: black;
            }
            .table { color: black; border-color: #000; }
            .table thead { background: #eee !important; color: black !important; }
            .status-stamp { opacity: 0.5; top: 50px; }
            .total-box { background: #f9f9f9; color: black; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>

<div class="container text-center mb-4 no-print">
    <div class="d-inline-flex gap-2 p-2 bg-dark rounded-pill shadow">
        <a href="<?php echo $back_to; ?>" class="btn btn-outline-light rounded-pill px-4">Back</a>
        <a href="../auth/logout.php" class="btn btn-logout px-4">Logout</a>
        
        <button onclick="window.print()" class="btn btn-cyan px-4"><i class="bi bi-printer me-2"></i>Print PDF</button>
        <button id="emailBtn" onclick="sendInvoiceEmail(<?php echo $order['id']; ?>)" class="btn btn-outline-cyan px-4">
            <i class="bi bi-envelope me-2"></i>Email Invoice
        </button>
    </div>
</div>

<div class="bill-container">
    <?php if($order['payment_method'] == 'ONLINE'): ?>
        <div class="status-stamp stamp-paid">PAYMENT VERIFIED</div>
    <?php else: ?>
        <div class="status-stamp stamp-cod">CASH ON DELIVERY</div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-4 mb-5">
        <div>
            <h1 class="fw-900 mb-0" style="font-family: 'Outfit'; letter-spacing: -1px;">MIMS<span style="color:var(--cyan)">.HEALTH</span></h1>
            <p class="text-muted small mb-0">Secure Pharmaceutical Logistics</p>
        </div>
        <div class="text-end">
            <h4 class="mb-0 fw-bold text-uppercase" style="letter-spacing: 2px;">Invoice</h4>
            <p class="mono mb-0 text-cyan">#ORD-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <p class="text-cyan small text-uppercase fw-bold mb-2" style="letter-spacing: 1px;">Patient Identity</p>
            <h5 class="fw-bold mb-1 text-white"><?php echo htmlspecialchars($order['customer_name']); ?></h5>
            <p class="small mb-0 opacity-75"><i class="bi bi-telephone me-2"></i><?php echo $order['phone']; ?></p>
            <p class="small opacity-75"><i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($order['address']); ?></p>
        </div>
        <div class="col-6 text-end">
            <p class="text-cyan small text-uppercase fw-bold mb-2" style="letter-spacing: 1px;">Session Data</p>
            <p class="fw-bold mb-1 text-white"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></p>
            <span class="badge rounded-pill" style="background: rgba(45, 212, 191, 0.1); color: var(--cyan);">Method: <?php echo $order['payment_method']; ?></span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Medical Compound</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Credits</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                mysqli_data_seek($items_result, 0); 
                while($item = mysqli_fetch_assoc($items_result)): 
                ?>
                <tr>
                    <td>
                        <div class="fw-bold text-white"><?php echo htmlspecialchars($item['m_name']); ?></div>
                        <div class="text-muted small">REF-ID: #MED-<?php echo $item['medicine_id']; ?></div>
                    </td>
                    <td class="text-center mono"><?php echo $item['quantity']; ?></td>
                    <td class="text-end mono">₹<?php echo number_format($item['price_at_purchase'], 2); ?></td>
                    <td class="text-end fw-bold mono text-cyan">₹<?php echo number_format($item['item_total'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="row mt-5 pt-4">
        <div class="col-md-6">
            <div class="p-4 rounded-4" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border);">
                <h6 class="text-cyan fw-bold small text-uppercase mb-3">Electronic Verification</h6>
                <p class="small opacity-50 mb-0" style="line-height: 1.6;">
                    This document serves as a digital tax invoice for medical supplies. 
                    Dispensed by MIMS Certified Pharmacy. Authenticated via Node #<?php echo str_pad($order['user_id'], 4, '0', STR_PAD_LEFT); ?>.
                </p>
            </div>
        </div>
        <div class="col-md-6 mt-4 mt-md-0">
            <div class="total-box">
                <div class="d-flex justify-content-between mb-2">
                    <span class="opacity-75 small">Subtotal</span>
                    <span class="mono">₹<?php echo number_format($original_subtotal, 2); ?></span>
                </div>
                <?php if($discount_value > 0): ?>
                <div class="d-flex justify-content-between mb-2 text-warning fw-bold">
                    <span class="small">Rebate</span>
                    <span class="mono">- ₹<?php echo number_format($discount_value, 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center border-top border-secondary pt-3 mt-3">
                    <span class="fw-bold text-white">Total Credits</span>
                    <h3 class="fw-900 mb-0 text-cyan" style="font-family: 'Outfit';">₹<?php echo number_format($order['total_amount'], 2); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function sendInvoiceEmail(orderId) {
    const btn = document.getElementById('emailBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Transmitting...';

    setTimeout(() => {
        window.location.href = 'send_invoice_email.php?order_id=' + orderId;
    }, 500);
}

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('status') === 'sent' || urlParams.get('status') === 'emailsent') {
    Swal.fire({
        icon: 'success',
        title: 'Invoice Dispatched',
        text: 'The digital receipt has been sent to your registered email.',
        background: '#0b0e14',
        color: '#F8FAFC',
        confirmButtonColor: '#2DD4BF'
    });
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>