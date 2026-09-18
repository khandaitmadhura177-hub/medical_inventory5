<?php
session_start();
include('../config/db_connect.php');

// SECURITY: Admin Only (Role 1)
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$status_msg = "Initializing Sync...";
$redirect_url = "returns_report.php"; 

if(isset($_GET['id'])) {
    $order_id = mysqli_real_escape_string($conn, $_GET['id']);
    $action = isset($_GET['action']) ? $_GET['action'] : 'approve';

    if($action == 'reject') {
        mysqli_query($conn, "UPDATE orders SET status = 'Processing' WHERE id = '$order_id'");
        $status_msg = "Return Request Rejected Successfully";
    } else {
        $order_query = "SELECT medicine_id, quantity FROM orders WHERE id = '$order_id'";
        $order_res = mysqli_query($conn, $order_query);
        $order_data = mysqli_fetch_assoc($order_res);

        if($order_data) {
            $m_id = $order_data['medicine_id'];
            $qty_to_restore = $order_data['quantity'];

            mysqli_begin_transaction($conn);
            try {
                // 1. Update Order Status to 'Returned'
                mysqli_query($conn, "UPDATE orders SET status = 'Returned' WHERE id = '$order_id'");
                
                // 2. Restore Stock
                mysqli_query($conn, "UPDATE medicines SET stock = stock + $qty_to_restore WHERE id = '$m_id'");

                mysqli_commit($conn);
                $status_msg = "Return Approved: Stock +$qty_to_restore Restored";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $status_msg = "Error: Stock restoration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Inventory Sync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --bg-dark: #0F172A; 
            --mims-primary: #2DD4BF; /* Electric Cyan */
            --mims-glow: rgba(45, 212, 191, 0.2);
            --glass: #1E293B; 
            --border: rgba(45, 212, 191, 0.15); 
        }

        body { 
            background: var(--bg-dark);
            font-family: 'Plus Jakarta Sans', sans-serif; 
            height: 100vh; display: flex; align-items: center; justify-content: center; 
            color: white; margin: 0; overflow: hidden; 
        }

        /* Neural Background Pattern */
        body::before {
            content: ""; position: absolute; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(45, 212, 191, 0.05) 0%, transparent 70%);
            z-index: -1;
        }

        .process-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px); 
            padding: 60px 50px; 
            border-radius: 40px; 
            border: 1px solid var(--border); 
            text-align: center; 
            max-width: 480px; width: 90%; 
            box-shadow: 0 40px 80px rgba(0,0,0,0.6); 
        }

        .loader-visual {
            position: relative;
            width: 100px; height: 100px;
            margin: 0 auto 40px;
        }

        .outer-ring {
            position: absolute; width: 100%; height: 100%;
            border: 2px solid var(--mims-glow);
            border-top: 2px solid var(--mims-primary);
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .inner-core {
            position: absolute; top: 15%; left: 15%; width: 70%; height: 70%;
            background: rgba(45, 212, 191, 0.05);
            border: 1px solid var(--border);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            color: var(--mims-primary); font-size: 2rem;
            animation: pulse 1.5s infinite ease-in-out;
            box-shadow: 0 0 20px var(--mims-glow);
        }

        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(0.9); opacity: 0.6; } }

        .status-badge { 
            background: rgba(45, 212, 191, 0.1); 
            color: var(--mims-primary); 
            border: 1px solid var(--border); 
            padding: 6px 20px; border-radius: 50px; 
            font-size: 0.7rem; font-weight: 800; 
            letter-spacing: 2px;
            margin-bottom: 25px; display: inline-block; 
            text-transform: uppercase;
        }

        .btn-mims { 
            background: var(--mims-primary); 
            border: none; color: #0F172A; 
            font-weight: 800; padding: 14px 40px; 
            border-radius: 15px; text-decoration: none; 
            display: inline-block; margin-top: 30px; 
            transition: 0.4s; 
            text-transform: uppercase; letter-spacing: 1px;
        }

        .btn-mims:hover { 
            background: white; transform: translateY(-3px); 
            box-shadow: 0 15px 30px var(--mims-glow);
        }

        .sync-text { font-family: 'Outfit', sans-serif; font-weight: 900; letter-spacing: -1px; }
    </style>
    <meta http-equiv="refresh" content="2;url=returns_report.php">
</head>
<body>

<div class="process-card animate__animated animate__zoomIn">
    <div class="status-badge">Kernel Update</div>
    
    <div class="loader-visual">
        <div class="outer-ring"></div>
        <div class="inner-core">
            <i class="bi bi-cpu-fill"></i>
        </div>
    </div>

    <h3 class="sync-text text-white mb-2">Inventory Sync</h3>
    <p class="opacity-70 mb-4 small" style="letter-spacing: 0.5px;"><?php echo $status_msg; ?></p>
    
    <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
        <div class="spinner-grow text-info spinner-grow-sm" role="status" style="background-color: var(--mims-primary);"></div>
        <span class="small opacity-50 fw-bold text-uppercase" style="font-size: 0.65rem;">Restructuring Ledger...</span>
    </div>

    <a href="returns_report.php" class="btn-mims">Manual Override</a>
</div>

</body>
</html>