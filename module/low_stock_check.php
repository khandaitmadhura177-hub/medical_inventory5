<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Authentication
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

$threshold = 10; // Global safety threshold

// 2. FETCH DATA: Prioritize 0 stock items first
$query = "SELECT * FROM medicines WHERE quantity <= $threshold ORDER BY quantity ASC, m_name ASC";
$result = mysqli_query($conn, $query);
$total_low = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Stock Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #0b0c10;
            --rose-gold: #b76e79;
            --card-bg: #14161a;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #e2e8f0;
            min-height: 100vh;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .alert-card { 
            border: 1px solid rgba(183, 110, 121, 0.15); 
            border-radius: 40px; 
            background: var(--card-bg); 
            box-shadow: 0 40px 80px rgba(0,0,0,0.5); 
            overflow: hidden; 
        }

        .report-header { 
            background: linear-gradient(to right, #1a1c22, #14161a); 
            padding: 50px 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .item-row { 
            border-bottom: 1px solid rgba(255,255,255,0.03) !important; 
            transition: 0.2s; 
        }
        
        .item-row:hover { background: rgba(183, 110, 121, 0.04); }
        
        .progress-track { 
            height: 8px; 
            border-radius: 10px; 
            background: rgba(255,255,255,0.05); 
            overflow: hidden;
        }
        
        .bar-danger { background: var(--danger); box-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }
        .bar-warning { background: var(--warning); }

        .btn-mims {
            background: rgba(255,255,255,0.03);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-mims:hover {
            background: var(--rose-gold);
            border-color: var(--rose-gold);
            transform: translateY(-2px);
        }

        .critical-glow {
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { text-shadow: 0 0 0px rgba(239, 68, 68, 0); }
            50% { text-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }
            100% { text-shadow: 0 0 0px rgba(239, 68, 68, 0); }
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white; color: black; }
            .alert-card { border: none; box-shadow: none; }
            .report-header { color: black; border-bottom: 2px solid black; }
            .text-white, .text-white-50 { color: black !important; }
            .bar-danger, .bar-warning { border: 1px solid black; }
        }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <div class="d-flex justify-content-between align-items-center mb-5 no-print animate__animated animate__fadeIn">
                <a href="inventory.php" class="btn-mims text-decoration-none">
                    <i class="bi bi-grid-3x3-gap me-2"></i>Inventory
                </a>
                <div class="d-flex gap-3">
                    <button onclick="window.print()" class="btn-mims">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Print Report
                    </button>
                    <a href="../admin/dashboard.php" class="btn-mims text-decoration-none" style="background: var(--rose-gold); border: none;">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </div>
            </div>

            <div class="card alert-card animate__animated animate__zoomIn">
                <div class="report-header text-center">
                    <div class="mb-4">
                        <span class="badge rounded-pill px-4 py-2 mono" style="background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid var(--danger); font-size: 0.7rem;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>STATUS: DEPLETION_WARNING
                        </span>
                    </div>
                    <h1 class="fw-800 mb-2" style="letter-spacing: -2px;">Inventory Health Report</h1>
                    <p class="text-white-50 mb-0">Found <strong><?php echo $total_low; ?></strong> pharmaceutical assets performing below safety parameters.</p>
                </div>

                <div class="p-4 p-md-5">
                    <?php if($total_low > 0): ?>
                        
                        <div class="table-responsive">
                            <table class="table table-dark align-middle mb-0">
                                <thead>
                                    <tr class="text-white-50 small mono uppercase" style="font-size: 0.65rem; letter-spacing: 2px;">
                                        <th class="ps-4">ASSET_NAME</th>
                                        <th>CATEGORY</th>
                                        <th class="text-center">CURRENT_VOL</th>
                                        <th>AVAILABILITY_INDEX</th>
                                        <th class="text-end pe-4 no-print">LOGISTICS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)): 
                                        $percent = ($row['quantity'] / $threshold) * 100;
                                        // Clamp percent to 100 for visual consistency
                                        $display_percent = min($percent, 100);
                                        $is_critical = ($row['quantity'] <= 3); // Highly critical
                                        $is_empty = ($row['quantity'] == 0);
                                        $bar_class = ($row['quantity'] <= 5) ? 'bar-danger' : 'bar-warning';
                                    ?>
                                    <tr class="item-row">
                                        <td class="py-4 ps-4">
                                            <div class="fw-bold text-white fs-5"><?php echo htmlspecialchars($row['m_name']); ?></div>
                                            <div class="mono text-white-50" style="font-size: 0.7rem;">UNIT_VAL: ₹<?php echo number_format($row['price'], 2); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-white-50 border border-secondary border-opacity-25 rounded-pill px-3">
                                                <?php echo htmlspecialchars($row['category']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="h4 fw-800 mono <?php echo $is_critical ? 'text-danger critical-glow' : 'text-warning'; ?>">
                                                <?php echo str_pad($row['quantity'], 2, '0', STR_PAD_LEFT); ?>
                                            </span>
                                        </td>
                                        <td style="width: 200px;">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="progress-track w-100">
                                                    <div class="h-100 <?php echo $bar_class; ?> animate__animated animate__slideInLeft" style="width: <?php echo $display_percent; ?>%"></div>
                                                </div>
                                                <small class="mono fw-bold text-white-50"><?php echo round($percent); ?>%</small>
                                            </div>
                                        </td>
                                        <td class="text-end pe-4 no-print">
                                            <a href="edit_medicine.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-4 fw-bold mono" style="font-size: 0.7rem;">
                                                RESTOCK_CMD
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-shield-check display-1 text-success opacity-25"></i>
                            <h2 class="fw-800 mt-4">Supply Chain: SECURE</h2>
                            <p class="text-white-50 mono">All inventory nodes are operating within nominal capacity.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="p-4 text-center border-top border-secondary border-opacity-25" style="background: rgba(0,0,0,0.1);">
                    <p class="mono small text-white-50 mb-0">MIMS_CORE_INTELLIGENCE // GEN_TIME: <?php echo date('Y-m-d H:i:s'); ?> // THRESHOLD: <?php echo $threshold; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>