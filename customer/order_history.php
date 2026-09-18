<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch ALL orders for history
$history_sql = "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC";
$history_query = mysqli_query($conn, $history_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Archive</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --bg: #030712; 
            --card-bg: #0b0e14; 
            --accent-cyan: #2DD4BF; 
            --border: rgba(45, 212, 191, 0.1);
        }

        body { 
            background-color: var(--bg); 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            min-height: 100vh;
        }

        .content-box { 
            background: var(--card-bg); 
            border-radius: 30px; 
            padding: 40px; 
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .table { --bs-table-bg: transparent; color: white; border-color: var(--border); }
        .table thead th { border-bottom: 2px solid var(--border); color: var(--accent-cyan); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .table tbody td { padding: 20px 10px; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.03); }

        .status-pill { 
            padding: 6px 14px; 
            border-radius: 50px; 
            font-size: 0.65rem; 
            font-weight: 800; 
            text-transform: uppercase; 
            display: inline-block; 
            letter-spacing: 0.5px;
        }
        
        /* Dynamic Status Coloring */
        .st-cancelled { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .st-delivered { background: rgba(45, 212, 191, 0.1); color: var(--accent-cyan); border: 1px solid rgba(45, 212, 191, 0.2); }
        .st-pending { background: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2); }

        .btn-action {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            color: white;
            transition: 0.3s;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .btn-action:hover { background: var(--accent-cyan); color: #000; border-color: var(--accent-cyan); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-900 m-0" style="font-family: 'Outfit';">ORDER <span class="text-cyan" style="color:var(--accent-cyan)">HISTORY</span></h2>
            <p class="text-muted small">Full log of your pharmaceutical transactions</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold small">
            <i class="bi bi-grid-fill me-2"></i>DASHBOARD
        </a>
    </div>

    <div class="content-box">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ref ID</th>
                        <th>Purchase Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end">Management</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($history_query) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($history_query)): 
                            $status = strtolower($row['status'] ?? 'pending');
                            $status_class = "st-$status";
                        ?>
                        <tr>
                            <td class="fw-bold">#ORD-<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td class="text-white-50"><?php echo date('d M, Y', strtotime($row['order_date'])); ?></td>
                            <td class="fw-bold">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-pill <?php echo $status_class; ?>">
                                    <?php echo strtoupper($status); ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn btn-action rounded-start-pill px-3">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="invoice.php?id=<?php echo $row['id']; ?>" class="btn btn-action rounded-end-pill px-3">
                                        <i class="bi bi-printer"></i> Bill
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 opacity-50">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No order logs available.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>