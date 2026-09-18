<?php
session_start();
include('../config/db_connect.php');

// 1. ACCESS CONTROL: Ensure the user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] == 1);

// 2. DATA AGGREGATION: Dynamic filtering based on role
$where_clause = $is_admin ? "" : "WHERE user_id = '$user_id'";

// Fetch Monthly Performance
$monthly_query = "SELECT 
                    DATE_FORMAT(order_date, '%b') as month, 
                    SUM(total_amount) as amount 
                  FROM orders 
                  $where_clause
                  GROUP BY MONTH(order_date) 
                  ORDER BY order_date ASC";

$monthly_res = mysqli_query($conn, $monthly_query);
$months = [];
$amounts = [];

while($row = mysqli_fetch_assoc($monthly_res)) {
    $months[] = $row['month'];
    $amounts[] = (float)$row['amount'];
}

// Fetch Global/Personal Stats
$stats_query = $is_admin 
    ? "SELECT COUNT(*) as total_orders, SUM(total_amount) as revenue FROM orders"
    : "SELECT COUNT(*) as total_orders, SUM(total_amount) as revenue FROM orders WHERE user_id = '$user_id'";

$stats_data = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | <?php echo $is_admin ? 'Financial' : 'Personal'; ?> Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-bg: #0b0c10;
            --rose-gold: #c5a1a1;
            --card-bg: #111216;
            --glass-border: rgba(197, 161, 161, 0.15);
        }

        body { 
            background-color: var(--mims-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #ffffff; 
            background-image: radial-gradient(circle at bottom left, rgba(197, 161, 161, 0.03), transparent);
        }
        
        .stat-card {
            border: 1px solid var(--glass-border);
            border-radius: 30px;
            padding: 35px;
            background: var(--card-bg);
            transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .stat-card:hover { transform: translateY(-8px); border-color: var(--rose-gold); box-shadow: 0 20px 40px rgba(0,0,0,0.3); }
        
        .icon-box {
            width: 65px; height: 65px;
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem;
            background: rgba(197, 161, 161, 0.05);
            color: var(--rose-gold);
            border: 1px solid var(--glass-border);
        }

        .report-main-card {
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            background: var(--card-bg);
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.4);
        }

        .chart-header {
            padding: 35px 45px;
            border-bottom: 1px solid var(--glass-border);
            display: flex; justify-content: space-between; align-items: center;
        }

        .export-btn {
            background: var(--rose-gold);
            color: #000; border: none; font-weight: 800;
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }
        .export-btn:hover { background: #e2c0c0; color: #000; transform: scale(1.05); }

        .mono { font-family: 'JetBrains Mono', monospace; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .report-main-card, .stat-card { border: 2px solid #000 !important; background: white !important; color: black !important; box-shadow: none !important; }
            canvas { filter: grayscale(1); }
            .text-white-50 { color: #555 !important; }
            .icon-box { border: 1px solid #000 !important; color: #000 !important; }
        }
    </style>
</head>
<body class="py-5">

<div class="container animate__animated animate__fadeIn">
    <div class="row align-items-center mb-5 no-print">
        <div class="col-md-7">
            <h6 class="text-uppercase mb-1 fw-800" style="letter-spacing: 4px; color: var(--rose-gold); font-size: 0.75rem;">Intelligence Module</h6>
            <h2 class="fw-800" style="letter-spacing: -1.5px;"><?php echo $is_admin ? 'Financial Analytics' : 'Operational Expenditures'; ?></h2>
        </div>
        <div class="col-md-5 text-md-end">
            <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold me-2 opacity-75 btn-sm">Close Terminal</a>
            <button onclick="window.print()" class="btn export-btn rounded-pill px-4 shadow-sm">
                <i class="bi bi-printer-fill me-2"></i>Generate PDF
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="stat-card d-flex align-items-center">
                <div class="icon-box me-4">
                    <i class="bi bi-terminal-plus"></i>
                </div>
                <div>
                    <p class="text-white-50 fw-bold mb-1 small text-uppercase" style="letter-spacing: 2px;">Data Cycles</p>
                    <h2 class="fw-800 mb-0 mono"><?php echo number_format($stats_data['total_orders'] ?? 0); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card d-flex align-items-center">
                <div class="icon-box me-4">
                    <i class="bi bi-currency-exchange"></i>
                </div>
                <div>
                    <p class="text-white-50 fw-bold mb-1 small text-uppercase" style="letter-spacing: 2px;"><?php echo $is_admin ? 'Gross Yield' : 'Net Allocation'; ?></p>
                    <h2 class="fw-800 mb-0 mono">₹<?php echo number_format($stats_data['revenue'] ?? 0, 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="report-main-card">
        <div class="chart-header">
            <div>
                <h5 class="fw-800 mb-1">Performance Curve</h5>
                <p class="text-white-50 small mb-0 mono">FISCAL_LOG: <?php echo date('Y'); ?></p>
            </div>
            <div class="d-flex align-items-center">
                <div class="spinner-grow spinner-grow-sm text-success me-2" role="status"></div>
                <span class="small fw-800 text-uppercase" style="color: var(--rose-gold); letter-spacing: 1px;">Live Stream</span>
            </div>
        </div>
        
        <div class="p-4 p-md-5">
            <?php if(!empty($months)): ?>
                <div style="height: 400px; position: relative;">
                    <canvas id="salesChart"></canvas>
                </div>
            <?php else: ?>
                <div class="text-center py-5" style="opacity: 0.2;">
                    <i class="bi bi-graph-down-arrow display-1 mb-3 d-block"></i>
                    <h4 class="fw-800">NULL_DATA_SET</h4>
                    <p class="mono">No transaction history detected for the current period.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');

// Financial Gradient Logic
let gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, 'rgba(197, 161, 161, 0.3)');
gradient.addColorStop(1, 'rgba(11, 12, 16, 0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Cycle Volume',
            data: <?php echo json_encode($amounts); ?>,
            borderColor: '#c5a1a1',
            borderWidth: 4,
            backgroundColor: gradient,
            fill: true,
            tension: 0.45,
            pointBackgroundColor: '#0b0c10',
            pointBorderColor: '#c5a1a1',
            pointBorderWidth: 3,
            pointRadius: 6,
            pointHoverRadius: 10,
            pointHoverBorderWidth: 4
        }]
    },
    options: {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111216',
                titleFont: { family: 'JetBrains Mono', size: 14 },
                bodyFont: { family: 'Plus Jakarta Sans', weight: '700', size: 16 },
                titleColor: '#c5a1a1',
                bodyColor: '#ffffff',
                borderColor: 'rgba(197, 161, 161, 0.3)',
                borderWidth: 1,
                padding: 15,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return ' ₹' + context.parsed.y.toLocaleString();
                    }
                }
            }
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: 'rgba(255, 255, 255, 0.03)', drawBorder: false },
                ticks: { 
                    color: '#475569', 
                    font: { family: 'JetBrains Mono', weight: '600' },
                    callback: function(value) { return '₹' + value.toLocaleString(); }
                }
            },
            x: { 
                grid: { display: false },
                ticks: { color: '#475569', font: { family: 'JetBrains Mono', weight: '600' } }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>