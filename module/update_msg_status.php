<?php
session_start();
include('../config/db_connect.php');

// SECURITY: Only Admin (Role 1) can update status
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_GET['id']) && isset($_GET['status'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $status = mysqli_real_escape_string($conn, $_GET['status']);

    $allowed_statuses = ['Pending', 'Resolved'];
    if(!in_array($status, $allowed_statuses)) {
        header("Location: message.php?msg=Invalid+Status");
        exit();
    }

    $query = "UPDATE contact_messages SET status = '$status' WHERE id = '$id'";
    $update_success = mysqli_query($conn, $query);
} else {
    header("Location: message.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="1.5;url=message.php?msg=Status+Updated">
    <title>MIMS | Committing Changes...</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-bg: #0b0c10;
            --rose-gold: #c5a1a1;
            --emerald: #10b981;
            --card-bg: #111216;
            --glass-border: rgba(197, 161, 161, 0.1);
        }

        body { 
            background-color: var(--mims-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            overflow: hidden;
            background-image: radial-gradient(circle at center, rgba(197, 161, 161, 0.05) 0%, transparent 70%);
        }

        .loader-card {
            background: var(--card-bg);
            padding: 60px 40px;
            border-radius: 40px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 50px 100px rgba(0,0,0,0.6);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .status-icon-box {
            width: 100px;
            height: 100px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 35px;
            font-size: 3rem;
            border: 1px solid var(--glass-border);
            transition: 0.5s;
            <?php echo ($status == 'Resolved') ? 'background: rgba(16, 185, 129, 0.1); color: var(--emerald);' : 'background: rgba(197, 161, 161, 0.1); color: var(--rose-gold);'; ?>
        }

        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: rgba(255,255,255,0.03);
            border-radius: 20px;
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .progress-line {
            height: 100%;
            background: linear-gradient(90deg, var(--rose-gold), #fff);
            width: 0%;
            animation: fillProgress 1.4s cubic-bezier(0.65, 0, 0.35, 1) forwards;
        }

        @keyframes fillProgress {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        .sync-text {
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 3px;
            font-size: 0.65rem;
            font-weight: 800;
            color: var(--rose-gold);
            text-transform: uppercase;
            opacity: 0.8;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 800;
            margin-top: 10px;
            <?php echo ($status == 'Resolved') ? 'background: rgba(16, 185, 129, 0.15); color: var(--emerald);' : 'background: rgba(197, 161, 161, 0.15); color: var(--rose-gold);'; ?>
        }
    </style>
</head>
<body>

    <div class="loader-card animate__animated animate__fadeInUp">
        <div class="status-icon-box animate__animated animate__pulse animate__infinite">
            <?php if($status == 'Resolved'): ?>
                <i class="bi bi-check-all"></i>
            <?php else: ?>
                <i class="bi bi-clock-history"></i>
            <?php endif; ?>
        </div>
        
        <h4 class="fw-800 mb-1">Committing Update</h4>
        <div class="status-pill mb-4"><?php echo strtoupper($status); ?></div>
        
        <div class="progress-bar-container">
            <div class="progress-line"></div>
        </div>

        <div class="d-flex flex-column align-items-center">
            <span class="sync-text mb-2">Synchronizing_Registry</span>
            <div class="spinner-grow spinner-grow-sm text-secondary opacity-25" role="status"></div>
        </div>
    </div>

</body>
</html>