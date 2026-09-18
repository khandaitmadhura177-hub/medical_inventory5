<?php
session_start();
include('../config/db_connect.php');

// --- NEURAL GUARD: FIXES BACK-KEY SECURITY ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// SECURITY: Admin Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Get Customer ID from URL
if(!isset($_GET['id'])) {
    header("Location: customer_list.php");
    exit();
}

$customer_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch Customer Profile
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$customer_id' AND role = 0");
$user = mysqli_fetch_assoc($user_query);

if(!$user) {
    echo "<script>alert('Patient node not found.'); window.location='customer_list.php';</script>";
    exit();
}

// Fetch Order History for this specific user
$orders_query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$customer_id' ORDER BY order_date DESC");
$total_spent_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE user_id = '$customer_id' AND status != 'Cancelled'"));
$total_spent = $total_spent_res['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Patient Profile #<?php echo $customer_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800;900&display=swap');
        
        :root { 
            --bg: #030712; 
            --cyan: #2DD4BF; 
            --card-bg: #0b0e14; 
            --border: rgba(45, 212, 191, 0.15); 
        }

        body { 
            background: var(--bg); 
            color: #F8FAFC; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
        }

        .btn-neural-back {
            background: rgba(45, 212, 191, 0.05);
            border: 1px solid var(--border);
            color: var(--cyan);
            border-radius: 12px;
            padding: 8px 18px;
            text-decoration: none;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .btn-neural-back:hover { background: var(--cyan); color: #030712; }

        .stat-box {
            background: #000;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
        }

        .avatar-circle {
            width: 100px; height: 100px;
            background: var(--cyan);
            color: #000;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-family: 'Outfit'; font-weight: 900;
            margin-bottom: 20px;
        }

        .table { color: #F8FAFC !important; background: transparent !important; }
        .table thead th { 
            color: var(--cyan); 
            border-bottom: 2px solid var(--cyan); 
            text-transform: uppercase; 
            font-size: 0.75rem;
            background: transparent !important;
        }
        .table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); background: transparent !important; }
        .table td { background: transparent !important; border: none; padding: 15px; }

        .status-pill {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-delivered { background: rgba(45, 212, 191, 0.2); color: var(--cyan); border: 1px solid var(--cyan); }
        .status-pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid #ffc107; }
        .status-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; }
    </style>
</head>
<body>

<div class="container py-5">
    <a href="customer_list.php" class="btn-neural-back mb-4">
        <i class="bi bi-arrow-left"></i> Back to Directory
    </a>

    <div class="row">
        <div class="col-lg-4">
            <div class="profile-card text-center">
                <div class="d-flex justify-content-center">
                    <div class="avatar-circle">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                </div>
                <h2 class="fw-900" style="font-family: 'Outfit';"><?php echo htmlspecialchars($user['username']); ?></h2>
                <p class="text-cyan fw-bold mb-4">Patient ID: #<?php echo $user['id']; ?></p>
                
                <div class="text-start mb-4">
                    <label class="text-muted small text-uppercase fw-bold">Email Address</label>
                    <div class="mb-3"><?php echo htmlspecialchars($user['email']); ?></div>
                    
                    <label class="text-muted small text-uppercase fw-bold">Phone Connection</label>
                    <div class="mb-3"><?php echo htmlspecialchars($user['phone'] ?? 'Not Linked'); ?></div>
                    
                    <label class="text-muted small text-uppercase fw-bold">Physical Address</label>
                    <div class="text-white"><?php echo htmlspecialchars($user['address'] ?? 'No Address on file'); ?></div>
                </div>

                <div class="row g-2">
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="text-muted small">Total Orders</div>
                            <div class="h4 fw-900 m-0 text-cyan"><?php echo mysqli_num_rows($orders_query); ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-box">
                            <div class="text-muted small">Investment</div>
                            <div class="h4 fw-900 m-0 text-cyan">₹<?php echo number_format($total_spent); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="profile-card">
                <h4 class="fw-800 mb-4" style="font-family: 'Outfit'; color: var(--cyan);">Order History Log</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($orders_query) > 0): ?>
                                <?php while($order = mysqli_fetch_assoc($orders_query)): 
                                    $status_class = 'status-pending';
                                    if($order['status'] == 'Delivered') $status_class = 'status-delivered';
                                    if($order['status'] == 'Cancelled') $status_class = 'status-cancelled';
                                ?>
                                <tr>
                                    <td class="fw-bold">#ORD-<?php echo $order['id']; ?></td>
                                    <td class="small"><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                    <td class="fw-bold text-cyan">₹<?php echo number_format($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-pill <?php echo $status_class; ?>">
                                            <?php echo $order['status']; ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-3" style="font-size: 0.65rem; font-weight: 800;">VIEW</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No purchase history detected for this node.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>