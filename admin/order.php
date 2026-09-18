<?php
session_start();
include('../config/db_connect.php');

// Security & Data Query
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

/** * UPDATED QUERY: 
 * 1. Uses GROUP_CONCAT to merge multiple medicines into one string.
 * 2. Joins 'order_items' and 'medicines' to fix the empty "Units" column.
 */
$query = "SELECT 
            o.*, 
            COALESCE(u.username, o.customer_name) as customer_display,
            GROUP_CONCAT(CONCAT(m.m_name, ' (x', oi.quantity, ')') SEPARATOR '<br>') as item_details
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id 
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN medicines m ON oi.medicine_id = m.id
          GROUP BY o.id 
          ORDER BY o.id DESC";

$result = mysqli_query($conn, $query);

// Revenue calculation
$status_filter = "status NOT IN ('Cancelled', 'Returned', 'Return Requested', 'Pending Review')";
$rev_res = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE $status_filter");
$rev_data = mysqli_fetch_assoc($rev_res);
$total_revenue = $rev_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Transaction Ledger</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --cyan: #2DD4BF; 
            --bg: #030712; 
            --card-bg: #0b0e14; 
            --border: rgba(45, 212, 191, 0.15); 
        }
        
        body { 
            background: var(--bg); 
            color: #fff; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: radial-gradient(circle at 100% 0%, rgba(45, 212, 191, 0.05) 0%, transparent 40%);
            min-height: 100vh;
        }

        .header-banner { 
            background: var(--card-bg); 
            border-radius: 28px; 
            padding: 40px; 
            margin-bottom: 35px; 
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .glass-card { 
            background: #000000; 
            border: 1px solid var(--border); 
            border-radius: 30px; 
            padding: 20px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            overflow: hidden;
        }

        .table thead th { 
            background: #0b0e14 !important;
            color: var(--cyan) !important;
            font-family: 'Outfit';
            font-size: 0.75rem;
            letter-spacing: 1.5px;
            padding: 20px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--border) !important;
        }

        .table tbody td { 
            padding: 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #ffffff;
            background: #000000 !important;
        }

        .prod-box { 
            background: rgba(45, 212, 191, 0.03); 
            border-left: 3px solid var(--cyan); 
            padding: 12px; 
            border-radius: 12px; 
        }

        .medicine-title {
            color: var(--cyan) !important;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            line-height: 1.6;
        }

        .price-text {
            font-family: 'Outfit';
            font-weight: 800;
            font-size: 1.1rem;
            color: #ffffff;
        }

        .status-badge { 
            font-size: 0.65rem; 
            padding: 5px 12px; 
            border-radius: 50px; 
            font-weight: 800; 
            text-transform: uppercase;
            display: inline-block;
        }
        
        .bg-pending { background: rgba(255, 193, 7, 0.1); color: #ffc107; border: 1px solid rgba(255, 193, 7, 0.2); }
        .bg-cancelled { background: rgba(255, 71, 71, 0.1); color: #ff4747; border: 1px solid rgba(255, 71, 71, 0.2); }
        .bg-paid { background: rgba(45, 212, 191, 0.1); color: var(--cyan); border: 1px solid var(--border); }
        
        .btn-outline-cyan {
            border-color: var(--cyan);
            color: var(--cyan);
        }
        
        .btn-outline-cyan:hover {
            background: var(--cyan);
            color: #000;
        }

        .table-hover tbody tr:hover td {
            background-color: #0b0e14 !important;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="header-banner d-flex justify-content-between align-items-center">
        <div>
            <span class="badge mb-2 fw-bold" style="background: var(--cyan); color: #000;">LEDGER v4.0</span>
            <h1 class="fw-900 mb-1" style="font-family: 'Outfit';">Transaction Ledger</h1>
            <div class="d-flex align-items-center gap-3">
                <span class="opacity-50 small fw-bold text-uppercase">Total Realized Revenue</span>
                <span class="fs-4 fw-900" style="color: var(--cyan);">₹<?php echo number_format($total_revenue, 2); ?></span>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-outline-cyan rounded-pill px-4 fw-bold">
            <i class="bi bi-cpu me-2"></i>TERMINAL HOME
        </a>
    </div>

    <div class="glass-card shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="ledgerTable">
                <thead>
                    <tr>
                        <th style="width: 12%;">Seq ID</th>
                        <th style="width: 38%;">Resource Specification</th>
                        <th style="width: 20%;">Entity Context</th>
                        <th style="width: 15%;">Fiscal Total</th>
                        <th style="width: 15%;" class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result): while($row = mysqli_fetch_assoc($result)): 
                        $status = strtoupper($row['status'] ?? 'Processing');
                        $badge_class = 'bg-pending';
                        if($status == 'CANCELLED') $badge_class = 'bg-cancelled';
                        if(in_array($status, ['PAID', 'DELIVERED', 'COMPLETED'])) $badge_class = 'bg-paid';
                    ?>
                    <tr>
                        <td class="fw-bold" style="color: var(--cyan);">
                            #<?php echo str_pad($row['id'], 5, '0', STR_PAD_LEFT); ?>
                        </td>

                        <td>
                            <div class="prod-box">
                                <div class="medicine-title">
                                    <?php echo !empty($row['item_details']) ? $row['item_details'] : '<span class="opacity-25">NO ITEMS LOGGED</span>'; ?>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="fw-bold text-white mb-1"><?php echo htmlspecialchars($row['customer_display']); ?></div>
                            <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                        </td>

                        <td>
                            <div class="price-text">₹<?php echo number_format($row['total_amount'], 2); ?></div>
                        </td>

                        <td class="text-end">
                            <a href="../customer/invoice.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-cyan rounded-3">
                                <i class="bi bi-file-earmark-text fs-5"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>