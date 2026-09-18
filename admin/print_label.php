<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. REDIRECT LOGIC
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: order.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// 3. DATA QUERY: Robust fetching with item details
$query = "SELECT o.*, 
          COALESCE(u.username, o.customer_name) as customer_display,
          (SELECT GROUP_CONCAT(m.m_name SEPARATOR ', ') 
           FROM order_items oi 
           JOIN medicines m ON oi.medicine_id = m.id 
           WHERE oi.order_id = o.id) as contents
          FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id 
          WHERE o.id = '$order_id'";

$res = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($res);

if (!$order) { exit("Order record not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Shipping Logistics Terminal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --cyan: #2DD4BF; 
            --bg-deep: #030712; 
            --glass: rgba(11, 14, 20, 0.95); 
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; display: flex; justify-content: center; align-items: center;
            padding: 50px;
            background: var(--bg-deep);
            background-image: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.05) 0%, transparent 70%);
            min-height: 100vh; color: #fff; overflow-x: hidden;
        }

        /* Ambient Background Elements */
        .neural-grid { position: absolute; width: 100%; height: 100%; top: 0; left: 0; z-index: 1; opacity: 0.2; pointer-events: none; }

        /* Label Design - High Contrast for Printing */
        .label-container { 
            width: 450px; background: white; color: black; border: 4px solid #000; 
            position: relative; box-shadow: 0 40px 100px rgba(0,0,0,0.8); z-index: 10; 
            border-radius: 2px; transform: scale(1); transition: transform 0.3s ease;
        }
        
        .brand-header { background: #000; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; font-family: 'Outfit'; }
        .barcode-area { text-align: center; padding: 30px; border-bottom: 4px solid #000; }
        .barcode-visual { font-family: 'Libre Barcode 128', cursive; font-size: 85px; line-height: 1; margin-bottom: 5px; }
        .label-title { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #777; letter-spacing: 1.2px; margin-bottom: 4px; }

        .no-print { position: fixed; top: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 15px; z-index: 100; }
        .btn-terminal { 
            background: var(--glass); color: var(--cyan); padding: 12px 24px; 
            border: 1px solid var(--cyan); border-radius: 50px; 
            cursor: pointer; font-weight: 700; text-decoration: none; 
            backdrop-filter: blur(10px); transition: 0.3s; 
            font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase;
        }
        .btn-terminal:hover { background: var(--cyan); color: #000; box-shadow: 0 0 20px rgba(45, 212, 191, 0.4); }

        @media print { 
            body { background: white !important; padding: 0; display: block; } 
            .no-print, .neural-grid { display: none !important; } 
            .label-container { box-shadow: none; border: 4px solid #000; margin: 0; width: 100%; transform: none; } 
            .label-title { color: #000; } 
        }
    </style>
</head>
<body>
    <div class="neural-grid"></div>

    <div class="no-print">
        <a href="order.php" class="btn-terminal"><i class="bi bi-arrow-left me-2"></i> Back to Ledger</a>
        <button onclick="window.print()" class="btn-terminal" style="background: var(--cyan); color: #000;">
            <i class="bi bi-printer-fill me-2"></i> Execute Print
        </button>
    </div>

    <div class="label-container">
        <div class="brand-header">
            <div style="font-weight: 900; letter-spacing: -0.5px; font-size: 1.3rem;">MIMS <span style="font-weight: 300; opacity: 0.8;">LOGISTICS</span></div>
            <div style="font-size: 0.6rem; font-weight: 900; border: 1.5px solid white; padding: 3px 8px; border-radius: 3px;">PHARMA-TRAC v2.0</div>
        </div>

        <div class="barcode-area">
            <div class="barcode-visual">ORD<?php echo $order['id']; ?></div>
            <div style="font-weight: 800; letter-spacing: 6px; font-size: 0.85rem;">TRK-MIMS-<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div style="padding: 25px; border-bottom: 4px solid #000;">
            <div class="label-title">Consignee / Recipient:</div>
            <div style="font-size: 1.7rem; font-weight: 900; text-transform: uppercase; line-height: 1.1; margin-bottom: 12px; font-family: 'Outfit';">
                <?php echo htmlspecialchars($order['customer_display']); ?>
            </div>
            
            <div class="label-title" style="margin-top: 15px;">Delivery Destination:</div>
            <div style="font-size: 1rem; font-weight: 600; line-height: 1.4; text-transform: uppercase;">
                <?php echo nl2br(htmlspecialchars($order['address'] ?? 'HUB-STATION NOT SPECIFIED')); ?>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 25px;">
                <div>
                    <div class="label-title">Comm Link:</div>
                    <b style="font-size: 1.1rem; letter-spacing: 1px;"><?php echo htmlspecialchars($order['phone'] ?? '+91 00000 00000'); ?></b>
                </div>
                <?php if(!empty($order['pincode'])): ?>
                <div style="text-align: right;">
                    <div class="label-title">Sector Code:</div>
                    <b style="font-size: 2.8rem; line-height: 0.9; font-family: 'Outfit';"><?php echo htmlspecialchars($order['pincode']); ?></b>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr;">
            <div style="padding: 15px; border-right: 4px solid #000;">
                <div class="label-title">Manifest Contents</div>
                <div style="font-size: 0.85rem; font-weight: 700; line-height: 1.2; text-transform: uppercase;">
                    <?php echo $order['contents'] ? htmlspecialchars($order['contents']) : 'General Medical Supplies'; ?>
                </div>
            </div>
            <div style="padding: 15px; background: #f4f4f4;">
                <div class="label-title">Units</div>
                <b style="font-size: 1.4rem; font-family: 'Outfit';">TOTAL PACKAGE</b>
            </div>
        </div>
        
        <div style="background: #000; color: #fff; padding: 10px; text-align: center; font-size: 0.6rem; font-weight: 800; letter-spacing: 2.5px;">
            CRITICAL PHARMACEUTICAL CARE - HANDLE WITH PRECISION
        </div>
    </div>
</body>
</html>