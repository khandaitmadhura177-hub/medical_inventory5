<?php
session_start();
include('../config/db_connect.php');

// --- 1. CORE LOGIC ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// Fetch User Data
$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_res);
$display_name = $user_data['username'] ?? 'User';
$user_email = $user_data['email'] ?? 'N/A';

// Health Calculations
$u_height = $user_data['height'] ?? 0; 
$u_weight = $user_data['weight'] ?? 0; 
$u_dob = $user_data['birthdate'] ?? null;
$u_bg = $user_data['blood_group'] ?? 'N/A';

$age_val = "N/A";
if($u_dob) {
    $birthDate = new DateTime($u_dob);
    $age_val = $birthDate->diff(new DateTime('today'))->y;
}

$bmi_val = 0;
$health_status = "PENDING";
if($u_height > 0 && $u_weight > 0) {
    $height_in_m = $u_height / 100;
    $bmi_val = round($u_weight / ($height_in_m * $height_in_m), 1);
    if($bmi_val < 18.5) { $health_status = "UNDERWEIGHT"; }
    else if($bmi_val < 25) { $health_status = "HEALTHY"; }
    else { $health_status = "OVERWEIGHT"; }
}

// Fetch Orders & Stats
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, SUM(total_amount) as spent FROM orders WHERE user_id = '$user_id'"));
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 8");
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800;900&display=swap');
        
        :root { --bg: #030712; --panel: #0b0e14; --text: #F8FAFC; --cyan: #2DD4BF; --border: rgba(45, 212, 191, 0.2); }

        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: var(--panel); border: 1px solid var(--border); border-radius: 20px; padding: 25px; height: 100%; }
        .text-cyan { color: var(--cyan) !important; }

        /* ACTION CARDS */
        .action-card { 
            background: rgba(45, 212, 191, 0.03); 
            border: 1.5px solid var(--border); 
            border-radius: 20px; padding: 25px; 
            text-decoration: none; color: var(--text); 
            display: block; transition: 0.3s ease; 
        }
        .action-card:hover { border-color: var(--cyan); background: rgba(45, 212, 191, 0.08); transform: translateY(-5px); color: var(--text); }
        .action-icon { font-size: 2rem; color: var(--cyan); margin-bottom: 15px; display: block; }

        /* TOP STATS */
        .top-stat-box { background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 15px; padding: 15px; text-align: center; }

        /* LOGISTICS TABLE */
        .table { color: var(--text) !important; --bs-table-bg: transparent !important; margin-bottom: 0; }
        .table thead th { color: var(--cyan); border-bottom: 2px solid var(--border) !important; font-family: 'Outfit'; font-size: 0.75rem; text-transform: uppercase; padding: 15px; }
        .table td { padding: 15px; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.05) !important; }
        .row-even { background: rgba(255, 255, 255, 0.01) !important; }
        .row-odd { background: rgba(255, 255, 255, 0.03) !important; }

        /* HEALTH BOXES */
        .health-mini { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 12px; }

        /* MODAL */
        .custom-modal-content { background: #0b0e14; border: 1px solid var(--border); border-radius: 25px; color: white; overflow: hidden; }
        .modal-sidebar { background: rgba(255,255,255,0.03); border-right: 1px solid var(--border); padding: 25px; }
        .nav-btn { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 12px; border: none; background: transparent; color: white; text-align: left; font-weight: 600; }
        .nav-btn.active { background: var(--cyan); color: #000; }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h3 class="fw-900 m-0" style="font-family: 'Outfit';">MIMS<span class="text-cyan">.HEALTH</span></h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-light rounded-pill btn-sm px-4" data-bs-toggle="modal" data-bs-target="#profileModal">MY ACCOUNT</button>
            <a href="../auth/logout.php" class="btn btn-outline-danger rounded-pill btn-sm px-4">LOGOUT</a>
        </div>
    </div>

    <div class="row mb-5 align-items-end">
        <div class="col-md-7">
            <h1 class="display-4 fw-900" style="font-family: 'Outfit';">Welcome, <span class="text-cyan"><?php echo $display_name; ?></span></h1>
            <p class="opacity-50 fs-5">Active Session Verified. Accessing Medical Dispatch Stream...</p>
        </div>
        <div class="col-md-5">
            <div class="row g-3">
                <div class="col-6">
                    <div class="top-stat-box">
                        <small class="text-cyan fw-bold">TOTAL ORDERS</small>
                        <h3 class="fw-bold mb-0"><?php echo $stats['total'] ?? 0; ?></h3>
                    </div>
                </div>
                <div class="col-6">
                    <div class="top-stat-box">
                        <small class="text-cyan fw-bold">TOTAL SPENT</small>
                        <h3 class="fw-bold mb-0">₹<?php echo number_format($stats['spent'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <a href="inventory.php" class="action-card">
                <i class="bi bi-capsule action-icon"></i>
                <h5 class="fw-bold mb-1">Open Dispensary</h5>
                <p class="small opacity-50 mb-0">Browse and order medical supplies.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="cart.php" class="action-card">
                <i class="bi bi-cart3 action-icon"></i>
                <h5 class="fw-bold mb-1">View Cart</h5>
                <p class="small opacity-50 mb-0">Review items before checkout.</p>
            </a>
        </div>
        <div class="col-md-4">
            <a href="orders.php" class="action-card">
                <i class="bi bi-box-seam action-icon"></i>
                <h5 class="fw-bold mb-1">My Orders</h5>
                <p class="small opacity-50 mb-0">Track your logistics streams.</p>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-card">
                <h6 class="opacity-50 fw-bold mb-4">LOGISTICS_STREAM</h6>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>REF ID</th><th>IDENTITY</th><th>STATUS</th><th class="text-end">ACTION</th></tr>
                        </thead>
                        <tbody>
                            <?php if($orders && mysqli_num_rows($orders) > 0): 
                                $i=0; while($row = mysqli_fetch_assoc($orders)): 
                                $row_class = ($i % 2 == 0) ? 'row-even' : 'row-odd';
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td class="text-cyan fw-bold">#ORD-<?php echo $row['id']; ?></td>
                                <td class="text-white">Multi-Item Logistics Package</td>
                                <td><span class="badge border border-info text-cyan"><?php echo strtoupper($row['status']); ?></span></td>
                                <td class="text-end"><i class="bi bi-eye opacity-50"></i></td>
                            </tr>
                            <?php $i++; endwhile; else: ?>
                            <tr><td colspan="4" class="text-center py-4">No active streams.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card">
                <h6 class="text-cyan fw-bold mb-4"><i class="bi bi-heart-pulse-fill me-2"></i>HEALTH_ADVISOR</h6>
                <div class="row g-2 mb-4 text-center">
                    <div class="col-6"><div class="health-mini"><small class="opacity-50">AGE</small><br><b><?php echo $age_val; ?></b></div></div>
                    <div class="col-6"><div class="health-mini"><small class="opacity-50">BMI</small><br><b class="text-cyan"><?php echo $bmi_val; ?></b></div></div>
                    <div class="col-6"><div class="health-mini"><small class="opacity-50">BLOOD</small><br><b class="text-danger"><?php echo $u_bg; ?></b></div></div>
                    <div class="col-6"><div class="health-mini"><small class="opacity-50">STATUS</small><br><small class="fw-bold"><?php echo $health_status; ?></small></div></div>
                </div>
                
                <div class="p-3 rounded-4" style="background: rgba(45, 212, 191, 0.05); border: 1px dashed var(--cyan);">
                    <h6 class="text-cyan small fw-bold mb-2"><i class="bi bi-droplet-fill me-1"></i> DAILY WELLNESS CHECK</h6>
                    <p class="small mb-0 opacity-75">
                        Stay strong, <b><?php echo $display_name; ?></b>! Remember to drink at least 3L of water today to keep your system hydrated and energized. Consistency is key to a healthier you.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-modal-content">
            <div class="row g-0">
                <div class="col-md-4 modal-sidebar">
                    <button class="nav-btn active"><i class="bi bi-person"></i> DETAILS</button>
                    <button class="nav-btn"><i class="bi bi-palette"></i> THEME</button>
                    <button class="nav-btn text-success"><i class="bi bi-headset"></i> SUPPORT</button>
                </div>
                <div class="col-md-8 p-5">
                    <h4 class="fw-bold mb-4">Patient Profile</h4>
                    <div class="mb-4">
                        <small class="text-uppercase opacity-50 fw-bold">Username</small>
                        <h2 class="fw-bold"><?php echo $display_name; ?></h2>
                    </div>
                    <div class="mb-4">
                        <small class="text-uppercase opacity-50 fw-bold">Email Address</small>
                        <p class="fs-5"><?php echo $user_email; ?></p>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close Overlay</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>