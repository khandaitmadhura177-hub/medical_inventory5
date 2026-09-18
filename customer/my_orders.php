<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Fetch orders with a JOIN to get item counts if needed
$query = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | My Orders</title>
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
            background-image: radial-gradient(circle at top right, rgba(45, 212, 191, 0.05), transparent);
        }

        .order-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 24px; 
            padding: 25px; 
            margin-bottom: 20px; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .order-card:hover {
            border-color: var(--accent-cyan);
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .nav-link-custom {
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .nav-link-custom:hover { color: var(--accent-cyan); }

        .status-badge {
            font-size: 0.7rem;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 100px;
            text-transform: uppercase;
        }
        .st-delivered { background: rgba(45, 212, 191, 0.1); color: var(--accent-cyan); border: 1px solid var(--accent-cyan); }
        .st-processing { background: rgba(251, 191, 36, 0.1); color: var(--accent-gold); border: 1px solid var(--accent-gold); }
        .st-cancelled { background: rgba(239, 68, 68, 0.1); color: var(--accent-red); border: 1px solid var(--accent-red); }

        /* Modified to allow text labels */
        .action-btn {
            min-width: 45px;
            height: 45px;
            padding: 0 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.05);
            color: white;
            border: 1px solid var(--border);
            transition: 0.3s;
            cursor: pointer;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            text-decoration: none;
            gap: 8px;
        }
        .action-btn:hover { background: var(--accent-cyan); color: #000; border-color: var(--accent-cyan); }
        .action-btn.text-danger:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }
        .action-btn.text-warning:hover { background: var(--accent-gold); color: black; border-color: var(--accent-gold); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 style="font-family: 'Outfit'; font-weight: 700;">PURCHASE <span class="text-info">VAULT</span></h2>
            <p class="text-muted small mb-0">Track and manage your pharmaceutical orders</p>
        </div>
        <div class="d-flex gap-4">
            <a href="../module/inventory.php" class="nav-link-custom"><i class="bi bi-plus-circle me-2"></i>NEW ORDER</a>
            <a href="dashboard.php" class="nav-link-custom"><i class="bi bi-arrow-left me-2"></i>BACK</a>
        </div>
    </div>

    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): 
                // Logic to match dashboard status
                $est_date = date('Y-m-d', strtotime($row['order_date'] . ' + 5 days'));
                $db_status = strtoupper(trim($row['status'] ?? 'PROCESSING'));
                
                if ($est_date < $today && $db_status != 'CANCELLED' && $db_status != 'RETURNED') {
                    $display_status = 'DELIVERED';
                    $badge_class = 'st-delivered';
                } else {
                    $display_status = $db_status;
                    $badge_class = ($db_status == 'CANCELLED' || $db_status == 'RETURNED') ? 'st-cancelled' : 'st-processing';
                }
            ?>
                <div class="col-12">
                    <div class="order-card">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="text-muted small fw-bold mb-1">REFERENCE</div>
                                <div class="fw-bold text-white">#ORD-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                <div class="small opacity-50"><?php echo date('M d, Y', strtotime($row['order_date'])); ?></div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="text-muted small fw-bold mb-1">AMOUNT</div>
                                <div class="h5 fw-bold text-cyan mb-0" style="color:var(--accent-cyan)">₹<?php echo number_format($row['total_amount'], 2); ?></div>
                            </div>

                            <div class="col-md-2 text-center">
                                <span class="status-badge <?php echo $badge_class; ?>">
                                    <i class="bi bi-record-circle me-1"></i> <?php echo $display_status; ?>
                                </span>
                            </div>

                            <div class="col-md-5">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="invoice.php?id=<?php echo $row['id']; ?>" class="action-btn" title="View Invoice">
                                        <i class="bi bi-file-earmark-text"></i> BILL
                                    </a>
                                    
                                    <?php if($display_status == 'PROCESSING'): ?>
                                        <button onclick="handleAction('cancel', <?php echo $row['id']; ?>)" class="action-btn text-danger">
                                            <i class="bi bi-x-circle"></i> CANCEL
                                        </button>
                                    <?php endif; ?>

                                    <?php if($display_status == 'DELIVERED'): ?>
                                        <button onclick="handleAction('return', <?php echo $row['id']; ?>)" class="action-btn text-warning">
                                            <i class="bi bi-arrow-counterclockwise"></i> RETURN
                                        </button>
                                    <?php endif; ?>

                                    <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-light rounded-pill px-4 fw-bold small d-flex align-items-center">
                                        TRACE DETAILS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="mb-4 opacity-20"><i class="bi bi-box-seam" style="font-size: 5rem;"></i></div>
                <h4 class="fw-bold">No Transaction Data</h4>
                <p class="text-muted">Your medical purchase history is currently empty.</p>
                <a href="../module/inventory.php" class="btn btn-cyan rounded-pill px-5 mt-3" style="background:var(--accent-cyan); color:#000; font-weight:800;">BROWSE MEDICINES</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function handleAction(type, id) {
    let msg = type === 'cancel' 
        ? "Authorization Required: Are you sure you want to cancel this order? Stock will be released back to the pharmacy."
        : "Return Request: Would you like to initiate a return for this delivered medical supply?";
        
    if(confirm(msg)) {
        window.location.href = `manage_order.php?action=${type}&id=${id}`;
    }
}
</script>

</body>
</html>