<?php
session_start();
include('../config/db_connect.php');

// Admin Security Gate
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) { 
    header("Location: ../auth/login.php?msg=Access Denied: Admin Clearance Required."); 
    exit(); 
}

$today = date('Y-m-d');
$next_month = date('Y-m-d', strtotime('+30 days'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Safety Control Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        :root {
            --bg: #030712;
            --neural-cyan: #2DD4BF;
            --card-bg: #0f172a;
            --danger-red: #f43f5e;
            --warning-amber: #fbbf24;
            --border: rgba(45, 212, 191, 0.1);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            color: white;
            min-height: 100vh;
            padding: 40px 0;
            background-image: radial-gradient(circle at 50% 0%, rgba(45, 212, 191, 0.05) 0%, transparent 70%);
        }

        .alert-wrapper {
            background: var(--card-bg);
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            overflow: hidden;
            border: 1px solid var(--border);
            backdrop-filter: blur(20px);
        }

        .alert-hero {
            background: linear-gradient(180deg, rgba(45, 212, 191, 0.05) 0%, transparent 100%);
            padding: 60px 40px;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        /* Neural Tabs */
        .nav-pills {
            background: rgba(0,0,0,0.2);
            padding: 6px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        .nav-pills .nav-link {
            border-radius: 15px;
            padding: 10px 25px;
            font-weight: 700;
            color: rgba(255,255,255,0.4);
            transition: 0.3s;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-pills .nav-link.active {
            background: var(--neural-cyan) !important;
            color: #0f172a !important;
            box-shadow: 0 10px 20px rgba(45, 212, 191, 0.2);
        }

        /* Notification Cards */
        .notification-card {
            border-radius: 24px;
            padding: 24px;
            margin-bottom: 16px;
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .notification-card:hover {
            transform: scale(1.02);
            background: rgba(255,255,255,0.04);
            border-color: var(--neural-cyan);
        }

        .bg-low-stock { border-left: 4px solid var(--warning-amber); }
        .bg-expired { border-left: 4px solid var(--danger-red); }
        .bg-expiring-soon { border-left: 4px solid var(--neural-cyan); }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-right: 20px;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .pulse-red { animation: pulse-red 2s infinite; color: var(--danger-red) !important; }
        @keyframes pulse-red {
            0% { box-shadow: 0 0 0 0px rgba(244, 63, 94, 0.3); }
            70% { box-shadow: 0 0 0 10px rgba(244, 63, 94, 0); }
            100% { box-shadow: 0 0 0 0px rgba(244, 63, 94, 0); }
        }

        .empty-state { padding: 60px 0; text-align: center; opacity: 0.4; }
        
        @media print { .no-print { display: none !important; } body { background: white; color: black; } }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="alert-wrapper animate__animated animate__fadeIn">
                <div class="alert-hero">
                    <span class="badge rounded-pill px-3 py-2 mb-3" style="background: rgba(45, 212, 191, 0.1); color: var(--neural-cyan); border: 1px solid var(--border);">
                        <i class="bi bi-shield-check me-2"></i>System Integrity Protocol
                    </span>
                    <h1 class="fw-800 display-5 mb-2">Safety <span style="color: var(--neural-cyan)">Audit</span></h1>
                    <p class="text-white-50 mb-4">Diagnostic monitoring for <?php echo date('F Y'); ?>.</p>
                    
                    <div class="d-flex justify-content-center gap-3 no-print">
                        <a href="../admin/dashboard.php" class="btn btn-outline-light rounded-pill px-4 btn-sm fw-bold">
                            <i class="bi bi-arrow-left me-2"></i>Back
                        </a>
                        <button onclick="window.print()" class="btn btn-light rounded-pill px-4 btn-sm fw-bold">
                            <i class="bi bi-printer me-2"></i>Export Logs
                        </button>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <div class="text-center mb-5 no-print">
                        <ul class="nav nav-pills d-inline-flex" id="pills-tab" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" id="pills-low-tab" data-bs-toggle="pill" data-bs-target="#pills-low">
                                    <i class="bi bi-moisture me-2"></i>Stock Depletion
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" id="pills-exp-tab" data-bs-toggle="pill" data-bs-target="#pills-exp">
                                    <i class="bi bi-clock-history me-2"></i>Expiry Watch
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-low">
                            <?php
                            $low = mysqli_query($conn, "SELECT * FROM medicines WHERE quantity < 10 ORDER BY quantity ASC");
                            if(mysqli_num_rows($low) > 0) {
                                while($r = mysqli_fetch_assoc($low)){
                                    echo "
                                    <div class='notification-card bg-low-stock animate__animated animate__fadeInUp'>
                                        <div class='icon-box' style='color: var(--warning-amber)'>
                                            <i class='bi bi-graph-down-arrow'></i>
                                        </div>
                                        <div class='flex-grow-1'>
                                            <h6 class='fw-800 mb-1 text-white'>".htmlspecialchars($r['m_name'])."</h6>
                                            <div class='d-flex align-items-center gap-2'>
                                                <span class='badge bg-warning text-dark px-2' style='font-size:0.6rem;'>CRITICAL</span>
                                                <small class='text-white-50'>{$r['quantity']} units remaining in node</small>
                                            </div>
                                        </div>
                                        <div class='no-print'>
                                            <a href='manage_inventory.php' class='btn btn-link text-warning text-decoration-none fw-bold small'>Update Stock</a>
                                        </div>
                                    </div>";
                                }
                            } else {
                                echo "<div class='empty-state'><i class='bi bi-check-circle display-1 text-success mb-3 d-block'></i><h4 class='fw-bold'>All nodes are fully stocked</h4></div>";
                            }
                            ?>
                        </div>

                        <div class="tab-pane fade" id="pills-exp">
                            <?php
                            $exp = mysqli_query($conn, "SELECT * FROM medicines WHERE expiry_date <= '$next_month' ORDER BY expiry_date ASC");
                            if(mysqli_num_rows($exp) > 0) {
                                while($r = mysqli_fetch_assoc($exp)){
                                    $is_expired = ($r['expiry_date'] < $today);
                                    $card_class = $is_expired ? 'bg-expired' : 'bg-expiring-soon';
                                    $icon_class = $is_expired ? 'bi-radioactive pulse-red' : 'bi-hourglass-split text-info';
                                    
                                    echo "
                                    <div class='notification-card $card_class animate__animated animate__fadeInUp'>
                                        <div class='icon-box'>
                                            <i class='bi $icon_class'></i>
                                        </div>
                                        <div class='flex-grow-1'>
                                            <h6 class='fw-800 mb-1 text-white'>".htmlspecialchars($r['m_name'])."</h6>
                                            <small class='text-white-50'>
                                                ".($is_expired ? '<span class="text-danger fw-bold">EXPIRED</span>' : 'Expiring Soon')." — ".date('d M Y', strtotime($r['expiry_date']))."
                                            </small>
                                        </div>
                                        <div class='no-print'>
                                            <span class='badge ".($is_expired ? 'bg-danger' : 'bg-info')." rounded-pill px-3 py-2' style='font-size:0.65rem'>
                                                ".($is_expired ? 'QUARANTINE' : 'MONITOR')."
                                            </span>
                                        </div>
                                    </div>";
                                }
                            } else {
                                echo "<div class='empty-state'><i class='bi bi-shield-check display-1 text-info mb-3 d-block'></i><h4 class='fw-bold'>No biochemical hazards detected</h4></div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>