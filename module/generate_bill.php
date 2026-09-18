<?php
session_start();

// 1. SECURITY: Only Admin can access
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

include('../config/db_connect.php');

if(!isset($_GET['sale_id'])) {
    die("<div style='color:white; background:#030712; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>ERROR: NULL_SALE_REFERENCE</div>");
}

$sale_id = (int)$_GET['sale_id'];

// Comprehensive JOIN to pull medicine details even if they were updated after the sale
$query = "SELECT s.*, m.m_name, m.price as current_price, m.category 
          FROM sales s 
          JOIN medicines m ON s.medicine_id = m.id 
          WHERE s.id = $sale_id";

$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    die("<div style='color:white; background:#030712; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif;'>ERROR: TRANSACTION_NOT_FOUND_IN_CORE</div>");
}

$data = mysqli_fetch_assoc($result);
// Note: In a real-world app, you might want to store the price at the time of sale in the 'sales' table 
// to prevent historical bills changing if prices go up.
$total_cost = $data['quantity_sold'] * $data['current_price'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS Invoice | #INV-<?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-dark: #030712;
            --rose-gold: #b76e79;
            --neural-cyan: #2DD4BF;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #f1f5f9; /* Contrast background for the page */
            color: #1E293B; 
            padding: 20px;
        }
        
        .invoice-wrapper {
            max-width: 850px;
            margin: 40px auto;
            background: white;
            padding: 60px;
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }

        /* Paid Watermark - Subtle but professional */
        .invoice-wrapper::before {
            content: "SETTLED";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 150px;
            font-weight: 900;
            color: rgba(45, 212, 191, 0.05);
            pointer-events: none;
            z-index: 0;
            letter-spacing: 15px;
        }

        .invoice-header { 
            border-bottom: 2px solid #f8fafc; 
            padding-bottom: 30px; 
            margin-bottom: 40px; 
        }

        .logo-text { font-weight: 800; letter-spacing: -2px; font-size: 2.5rem; }
        .logo-text span { color: var(--rose-gold); }
        
        .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; }
        .info-label { font-size: 0.65rem; text-transform: uppercase; color: #94a3b8; font-weight: 800; letter-spacing: 1.5px; }
        .info-value { font-weight: 700; color: var(--mims-dark); }

        .table-custom thead { background: #f8fafc; }
        .table-custom th { 
            border: none; 
            padding: 15px; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            color: #64748B; 
        }
        .table-custom td { padding: 25px 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }

        .total-card { 
            background: #fffafa; 
            border: 1px solid #fee2e2;
            border-radius: 25px; 
            padding: 30px; 
        }

        .btn-mims {
            border-radius: 15px;
            padding: 14px 28px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-print { background: var(--mims-dark); color: white; border: none; }
        .btn-print:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); }

        @media print {
            body { background: white !important; padding: 0 !important; }
            .invoice-wrapper { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .no-print { display: none !important; }
            .total-card { border: 2px solid #f1f5f9 !important; background: transparent !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="invoice-wrapper animate__animated animate__fadeInUp">
        <div class="invoice-header d-flex justify-content-between align-items-start">
            <div>
                <h1 class="logo-text mb-1">MI<span>MS</span></h1>
                <p class="text-muted small mb-0 fw-600">
                    <i class="bi bi-geo-alt-fill me-1"></i> MEDICAL CORE TERMINAL SECTOR 7<br>
                    <span class="mono">LIC_REF: <?php echo strtoupper(bin2hex(random_bytes(4))); ?></span>
                </p>
            </div>
            <div class="text-end">
                <h2 class="fw-800 mb-0" style="color: var(--rose-gold); letter-spacing: 2px;">INVOICE</h2>
                <div class="mono text-muted mt-1">ID: #INV-<?php echo str_pad($sale_id, 6, '0', STR_PAD_LEFT); ?></div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-4">
                <p class="info-label mb-1">Recipient Node</p>
                <p class="info-value mb-0 h5 fw-800"><?php echo htmlspecialchars($data['patient_name']); ?></p>
                <p class="small text-muted mb-0">Patient Record Active</p>
            </div>
            <div class="col-4">
                <p class="info-label mb-1">Hash Reference</p>
                <p class="info-value mb-0 mono text-uppercase"><?php echo substr(md5($sale_id . 'MIMS'), 0, 12); ?></p>
            </div>
            <div class="col-4 text-end">
                <p class="info-label mb-1">Settlement Date</p>
                <p class="info-value mb-0"><?php echo date('d M, Y', strtotime($data['sale_date'])); ?></p>
                <p class="small text-muted mb-0 mono"><?php echo date('H:i:s', strtotime($data['sale_date'])); ?> UTC</p>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Specifications</th>
                        <th class="text-center">Classification</th>
                        <th class="text-center">Units</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="fw-800 text-dark"><?php echo htmlspecialchars($data['m_name']); ?></div>
                            <div class="mono text-muted" style="font-size: 0.65rem;">ASSET_ID: <?php echo $data['medicine_id']; ?></div>
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-light text-dark border px-3"><?php echo htmlspecialchars($data['category']); ?></span>
                        </td>
                        <td class="text-center fw-800"><?php echo $data['quantity_sold']; ?></td>
                        <td class="text-end mono">₹<?php echo number_format($data['current_price'], 2); ?></td>
                        <td class="text-end fw-800">₹<?php echo number_format($total_cost, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="row justify-content-end">
            <div class="col-md-5">
                <div class="total-card">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="info-label">Subtotal</span>
                        <span class="fw-bold">₹<?php echo number_format($total_cost, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-3">
                        <span class="info-label">Tax (GST 0%)</span>
                        <span class="fw-bold">₹0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-800 h6 mb-0 text-uppercase">Final Total</span>
                        <span class="fw-800 h3 mb-0" style="color: var(--rose-gold);">₹<?php echo number_format($total_cost, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 pt-4 text-center border-top">
            <p class="mono text-muted mb-1" style="font-size: 0.6rem;">AUTHENTICATED_BY_MIMS_SYSTEM_CORE</p>
            <p class="small fw-800 text-uppercase" style="letter-spacing: 2px; color: var(--neural-cyan);">Live Well • Dose Responsibly</p>
        </div>

        <div class="mt-5 d-flex gap-3 justify-content-center no-print">
            <button onclick="window.print()" class="btn btn-mims btn-print shadow-sm">
                <i class="bi bi-printer-fill me-2"></i> PRINT INVOICE
            </button>
            <a href="billing.php" class="btn btn-mims btn-outline-dark">
                <i class="bi bi-plus-lg me-2"></i> NEW ORDER
            </a>
        </div>
    </div>
</div>

</body>
</html>