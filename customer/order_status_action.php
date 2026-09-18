<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get details from the URL sent by manage_order.php
$order_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '00000';
$type = isset($_GET['type']) ? strtoupper($_GET['type']) : 'ACTION';

// Set visual cues based on action type
$theme_color = ($type == 'CANCEL') ? 'var(--accent-red)' : 'var(--accent-gold)';
$icon = ($type == 'CANCEL') ? 'bi-x-circle' : 'bi-arrow-counterclockwise';
$heading = ($type == 'CANCEL') ? 'ORDER CANCELLED' : 'RETURN INITIATED';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | <?php echo $type; ?> Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700&display=swap');
        
        :root { 
            --bg: #030712; 
            --card-bg: #0b0e14; 
            --accent-cyan: #2DD4BF;
            --accent-gold: #fbbf24;
            --accent-red: #ef4444;
            --border: rgba(45, 212, 191, 0.1);
        }

        body { 
            background-color: var(--bg); 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(circle at top right, rgba(45, 212, 191, 0.05), transparent);
        }

        .status-card { 
            background: var(--card-bg); 
            border: 1px solid <?php echo $theme_color; ?>; 
            border-radius: 32px; 
            padding: 50px; 
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 2.5rem;
            color: <?php echo $theme_color; ?>;
            border: 1px solid <?php echo $theme_color; ?>;
        }

        .ref-badge {
            background: rgba(255,255,255,0.05);
            padding: 8px 16px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--accent-cyan);
            letter-spacing: 1px;
        }

        .btn-vault {
            background: var(--accent-cyan);
            color: #000;
            font-weight: 800;
            border-radius: 12px;
            padding: 12px 30px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-vault:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(45, 212, 191, 0.2);
            color: #000;
        }
    </style>
</head>
<body>

<div class="status-card">
    <div class="icon-circle">
        <i class="bi <?php echo $icon; ?>"></i>
    </div>
    
    <div class="mb-3">
        <span class="ref-badge">REFERENCE #ORD-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></span>
    </div>

    <h2 style="font-family: 'Outfit'; font-weight: 700; margin-bottom: 15px;">
        <?php echo $heading; ?>
    </h2>

    <p class="text-muted mb-5">
        Your request has been processed successfully. 
        <?php echo ($type == 'CANCEL') 
            ? 'Stock has been returned to the pharmacy inventory.' 
            : 'The Medical Admin has been notified for return verification.'; ?>
    </p>

    <div class="d-grid gap-3">
        <a href="my_orders.php" class="btn-vault">RETURN TO VAULT</a>
        <a href="dashboard.php" class="text-muted small text-decoration-none fw-bold">GO TO DASHBOARD</a>
    </div>
</div>

</body>
</html>