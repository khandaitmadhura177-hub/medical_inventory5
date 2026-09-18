<?php
session_start();
include('../config/db_connect.php');

// Security: Admin Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

$success = false;
$error_msg = "";
$display_ref = "Unknown Record";

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Fetch reference before deletion for the UI
    $name_res = mysqli_query($conn, "SELECT id FROM orders WHERE id = $id");
    if($order_data = mysqli_fetch_assoc($name_res)) {
        // Formatting the ID for a high-tech ledger look
        $display_ref = "#ORD-" . str_pad($id, 6, '0', STR_PAD_LEFT);
        
        // Perform Deletion
        $query = "DELETE FROM orders WHERE id = $id";
        if(mysqli_query($conn, $query)) {
            $success = true;
        } else {
            $error_msg = "Critical DB Error: " . mysqli_error($conn);
        }
    } else {
        $error_msg = "Record not found in the central ledger.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Purging Order...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #030712;
            --neural-cyan: #2DD4BF;
            --card-bg: #0f172a;
            --danger-red: #f43f5e;
            --border: rgba(45, 212, 191, 0.1);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: white;
            margin: 0;
            overflow: hidden;
        }

        .status-card {
            background: var(--card-bg);
            padding: 50px;
            border-radius: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            text-align: center;
            max-width: 480px;
            width: 90%;
            backdrop-filter: blur(10px);
        }

        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 25px;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--border);
        }

        .pulse-cyan { animation: pulse-c 2s infinite; color: var(--neural-cyan); }
        @keyframes pulse-c {
            0% { box-shadow: 0 0 0 0px rgba(45, 212, 191, 0.2); }
            70% { box-shadow: 0 0 0 15px rgba(45, 212, 191, 0); }
            100% { box-shadow: 0 0 0 0px rgba(45, 212, 191, 0); }
        }

        .loader-bar {
            width: 100%;
            height: 3px;
            background: rgba(255,255,255,0.05);
            margin-top: 30px;
            border-radius: 10px;
            overflow: hidden;
        }

        .loader-fill {
            width: 0%;
            height: 100%;
            background: var(--neural-cyan);
            animation: fill-up 2s linear forwards;
        }

        @keyframes fill-up { to { width: 100%; } }

        .btn-neural {
            background: transparent;
            border: 1px solid var(--danger-red);
            color: var(--danger-red);
            border-radius: 15px;
            padding: 12px 30px;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-neural:hover {
            background: var(--danger-red);
            color: white;
            box-shadow: 0 0 20px rgba(244, 63, 94, 0.3);
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body>

<div class="status-card animate__animated animate__zoomIn">
    <?php if($success): ?>
        <div class="icon-box pulse-cyan">
            <i class="bi bi-shield-slash"></i>
        </div>
        <h2 class="fw-800 mb-2">Order Purged</h2>
        <p class="text-white-50">Log entry <span class="text-white mono fw-bold"><?php echo htmlspecialchars($display_ref); ?></span> has been erased from the neural core.</p>
        
        <div class="loader-bar">
            <div class="loader-fill"></div>
        </div>
        <p class="small text-white-25 mt-3 tracking-widest uppercase">Syncing Ledger...</p>
        
        <script>setTimeout(() => { window.location.href = '../admin/order.php'; }, 2200);</script>

    <?php else: ?>
        <div class="icon-box" style="color: var(--danger-red); border-color: var(--danger-red);">
            <i class="bi bi-exclamation-octagon"></i>
        </div>
        <h2 class="fw-800 mb-2">Purge Aborted</h2>
        <p class="text-white-50 mb-4"><?php echo $error_msg ?: "The system encountered an unauthorized interrupt."; ?></p>
        <a href="../admin/order.php" class="btn btn-neural text-decoration-none">
            <i class="bi bi-arrow-left me-2"></i>Return to Base
        </a>
    <?php endif; ?>
</div>

</body>
</html>