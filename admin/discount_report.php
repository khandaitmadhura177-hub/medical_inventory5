<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) { 
    header("Location: ../auth/login.php?error=unauthorized");
    exit(); 
}

// 2. DATA QUERY
$query = "SELECT orders.*, users.username 
          FROM orders 
          JOIN users ON orders.user_id = users.id 
          WHERE orders.discount_amount > 0
          ORDER BY order_date DESC";
$result = mysqli_query($conn, $query);

// 3. SUMMARY LOGIC
$stats_query = "SELECT 
    SUM(subtotal) as gross, 
    SUM(discount_amount) as total_given, 
    SUM(total_amount) as net 
    FROM orders WHERE discount_amount > 0";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Discount Audit Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --bg-deep: #030712; 
            --cyan: #2DD4BF; 
            --card-bg: #0b0e14; 
            --border: rgba(45, 212, 191, 0.2); 
        }

        body { 
            background: var(--bg-deep) !important; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #ffffff; 
            min-height: 100vh;
        }

        .report-header { 
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 40px 0; 
            margin-bottom: 30px; 
        }

        /* UNIFORM STAT CARD STYLE */
        .stat-card { 
            background: var(--card-bg); 
            border-radius: 24px; 
            padding: 25px; 
            border: 1px solid var(--border);
            border-left: 4px solid var(--cyan) !important; /* Added to match the middle box style */
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }

        /* FIXED TABLE VISIBILITY */
        .table-container {
            background: #000000 !important;
            border-radius: 28px;
            border: 1px solid var(--border);
            overflow: hidden;
            padding: 10px;
        }

        .table { 
            background-color: transparent !important;
            color: #ffffff !important; 
            margin-bottom: 0; 
        }

        .table thead th { 
            background-color: #0b0e14 !important;
            color: var(--cyan) !important; 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            letter-spacing: 1.5px; 
            padding: 20px;
            border-bottom: 2px solid var(--border) !important;
        }

        .table tbody td { 
            background-color: #000000 !important;
            color: #ffffff !important;
            padding: 18px 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.05) !important;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover td {
            background-color: #0b0e14 !important;
        }

        .category-badge {
            background: rgba(45, 212, 191, 0.1) !important;
            border: 1px solid var(--cyan) !important;
            color: var(--cyan) !important;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 50px;
        }

        .fw-800 { font-weight: 800; }
        .text-cyan { color: var(--cyan) !important; }

        @media print { 
            .no-print { display: none !important; } 
            body { background: white !important; color: black !important; }
            .table, .table td, .table th { color: black !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body>

<div class="report-header no-print">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <span class="badge mb-2 fw-bold" style="background: var(--cyan); color: #000;">FINANCIAL INTELLIGENCE</span>
            <h1 class="h3 fw-800 m-0" style="font-family: 'Outfit';">Discount Audit Terminal</h1>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-info fw-bold px-4 rounded-pill" style="background: var(--cyan); border: none; color: #000;">
                <i class="bi bi-printer-fill me-2"></i>Print Audit
            </button>
            <a href="dashboard.php" class="btn btn-outline-light px-4 rounded-pill">Dashboard</a>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stat-card">
                <small class="text-cyan text-uppercase fw-bold">Gross Sales (Before Disc.)</small>
                <h2 class="text-cyan fw-800 m-0 mt-2">₹<?php echo number_format($stats['gross'] ?? 0, 2); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small class="text-cyan text-uppercase fw-bold">Total Revenue Foregone</small>
                <h2 class="text-cyan fw-800 m-0 mt-2">₹<?php echo number_format($stats['total_given'] ?? 0, 2); ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <small class="text-cyan text-uppercase fw-bold">Net Realized Income</small>
                <h2 class="text-cyan fw-800 m-0 mt-2">₹<?php echo number_format($stats['net'] ?? 0, 2); ?></h2>
            </div>
        </div>
    </div>

    <div class="table-container shadow-lg">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order Date</th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Category</th>
                        <th class="text-end">Base Price</th>
                        <th class="text-end">Savings</th>
                        <th class="text-end">Final Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td><span class="text-cyan">#ORD-<?php echo $row['id']; ?></span></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><span class="category-badge"><?php echo strtoupper($row['discount_label'] ?? 'General'); ?></span></td>
                            <td class="text-end opacity-75">₹<?php echo number_format($row['subtotal'], 2); ?></td>
                            <td class="text-end text-cyan fw-bold">-₹<?php echo number_format($row['discount_amount'], 2); ?></td>
                            <td class="text-end fw-800">₹<?php echo number_format($row['total_amount'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>