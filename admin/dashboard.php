<?php
session_start();

// --- NEURAL GUARD: FIXES BACK-KEY SECURITY ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('../config/db_connect.php');
error_reporting(E_ERROR | E_PARSE); 

// --- ROLE SECURITY ---
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin_query = mysqli_query($conn, "SELECT username FROM users WHERE id = '$admin_id'");
$admin_data = mysqli_fetch_assoc($admin_query);
$admin_name = $admin_data['username'] ?? 'Admin';

// --- DATA FETCHING ---
$status_filter = "status NOT IN ('Cancelled', 'Returned', 'Return Requested', 'Pending Review')";
$daily = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = CURDATE() AND $status_filter"));
$weekly = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND $status_filter"));
$monthly = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE MONTH(order_date) = MONTH(CURRENT_DATE()) AND YEAR(order_date) = YEAR(CURRENT_DATE()) AND $status_filter"));

// Statistics for Sidebar Badges
$msg_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM contact_messages"));
$total_messages = $msg_res['total'] ?? 0;

$user_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 0"));
$total_customers = $user_res['total'] ?? 0;

$reversal_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as pending FROM orders WHERE status = 'Return Requested'"));
$low_stock = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM medicines WHERE stock <= 10")); 

// --- CHART DATA GENERATION ---
$w_labels = []; $w_data = [];
for($i=6; $i>=0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = '$date' AND $status_filter"));
    $w_labels[] = date('D', strtotime($date));
    $w_data[] = $res['total'] ?? 0;
}
$y_labels = []; $y_data = [];
for($y = 2024; $y <= 2026; $y++) {
    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE YEAR(order_date) = '$y' AND $status_filter"));
    $y_labels[] = (string)$y;
    $y_data[] = $res['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | AI Admin Core</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800;900&display=swap');
        
        :root { 
            --bg: #030712; 
            --cyan: #2DD4BF; 
            --card-bg: #0b0e14; 
            --terminal-bg: #000000; 
            --border: rgba(45, 212, 191, 0.12); 
            --text-gray: #64748b;
        }

        body { 
            background: var(--bg); 
            color: #F8FAFC; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            overflow-x: hidden;
        }

        .welcome-header { padding: 40px 0 30px 0; }
        .badge-system { 
            background: rgba(45, 212, 191, 0.05); 
            color: var(--cyan); 
            font-weight: 800; 
            border-radius: 50px; 
            padding: 6px 20px; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            border: 1px solid var(--border);
            letter-spacing: 1px;
        }
        .welcome-title { font-family: 'Outfit'; font-weight: 900; font-size: 3.5rem; margin: 10px 0; letter-spacing: -1px; }
        
        /* Stat Cards */
        .stat-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 35px; 
            padding: 30px; 
            display: flex; 
            align-items: center; 
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            height: 100%; 
            cursor: pointer;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        .stat-card:hover { 
            border-color: var(--cyan); 
            transform: translateY(-10px); 
            background: #000000;
            box-shadow: 0 20px 50px rgba(45, 212, 191, 0.1); 
        }
        .icon-sq { 
            width: 65px; height: 65px; 
            background: #000000; 
            border: 1px solid var(--border); 
            border-radius: 20px; 
            display: flex; align-items: center; justify-content: center; 
            margin-right: 20px; color: var(--cyan); font-size: 1.8rem;
            box-shadow: inset 0 0 15px rgba(45, 212, 191, 0.05);
        }
        .stat-value { font-family: 'Outfit'; font-weight: 800; font-size: 2.8rem; line-height: 1; }

        /* Panels */
        .graph-panel { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 40px; 
            padding: 30px; 
            box-shadow: inset 0 0 60px rgba(0,0,0,0.7);
        }
        .btn-reset { 
            border: 1px solid var(--cyan); 
            color: var(--cyan); 
            border-radius: 50px; 
            background: transparent; 
            padding: 6px 18px; 
            font-size: 0.75rem; 
            font-weight: 700; 
            text-transform: uppercase;
            transition: 0.3s;
        }
        .btn-reset:hover { background: var(--cyan); color: #0F172A; }
        
        /* AI TERMINAL */
        .ai-terminal { 
            background: var(--terminal-bg); 
            border: 1px solid var(--border); 
            border-radius: 30px; 
            padding: 25px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.8);
        }
        #ai-log { height: 160px; overflow-y: auto; font-size: 0.8rem; color: var(--text-gray); margin-bottom: 15px; scroll-behavior: smooth; }
        .ai-msg { color: var(--cyan); margin-bottom: 8px; border-left: 2px solid var(--cyan); padding-left: 12px; font-weight: 600; }
        
        .ai-input { 
            background: #080a0f; 
            border: 1px solid var(--border); 
            border-radius: 15px; 
            color: #ffffff !important; 
            padding: 12px 15px; 
            font-size: 0.85rem; 
        }
        .ai-input::placeholder { color: rgba(45, 212, 191, 0.4); }
        .ai-input:focus { 
            border-color: var(--cyan); 
            box-shadow: 0 0 15px rgba(45, 212, 191, 0.1); 
            background: #000000; 
            color: var(--cyan) !important; 
        }
        
        /* Sidebar Action Grid */
        .side-panel { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 35px; 
            padding: 25px; 
        }
        .action-btn { 
            background: #05070a; 
            border: 1px solid var(--border); 
            border-radius: 22px; 
            padding: 18px; 
            text-decoration: none; 
            color: #F8FAFC; 
            display: flex; flex-direction: column; align-items: center; 
            transition: 0.3s; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px;
            position: relative;
        }
        .action-btn i { font-size: 1.5rem; color: var(--cyan); transition: 0.3s; }
        .action-btn:hover { 
            background: var(--cyan); 
            border-color: var(--cyan); 
            box-shadow: 0 0 20px rgba(45, 212, 191, 0.3);
        }
        .action-btn:hover i, .action-btn:hover { color: #000000 !important; }

        .btn-count-badge {
            background: var(--cyan);
            color: #000;
            font-size: 0.6rem;
            padding: 2px 6px;
            border-radius: 6px;
            margin-top: 5px;
            font-weight: 900;
        }

        #ai-log::-webkit-scrollbar { width: 4px; }
        #ai-log::-webkit-scrollbar-track { background: transparent; }
        #ai-log::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
    </style>
</head>
<body>

<div class="container-fluid px-lg-5 py-4">
    <div class="welcome-header">
        <span class="badge-system">Neural Admin Core / Secure Node 01</span>
        <h1 class="welcome-title">Master Console: <?php echo $admin_name; ?></h1>
        <div class="text-muted small"><i class="bi bi-cpu text-info me-2"></i>System Time: <span id="clock" class="fw-bold"></span></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4"><div class="stat-card" onclick="updateChart('monthly')"><div class="icon-sq"><i class="bi bi-graph-up-arrow"></i></div><div><div class="small fw-bold opacity-50 text-uppercase">Monthly Flow</div><div class="stat-value">₹<?php echo number_format($monthly['total'] ?? 0); ?></div></div></div></div>
        <div class="col-md-4"><div class="stat-card" onclick="updateChart('weekly')"><div class="icon-sq"><i class="bi bi-lightning-charge-fill"></i></div><div><div class="small fw-bold opacity-50 text-uppercase">Weekly Velocity</div><div class="stat-value">₹<?php echo number_format($weekly['total'] ?? 0); ?></div></div></div></div>
        <div class="col-md-4"><div class="stat-card" onclick="updateChart('daily')"><div class="icon-sq"><i class="bi bi-activity"></i></div><div><div class="small fw-bold opacity-50 text-uppercase">Daily Pulse</div><div class="stat-value">₹<?php echo number_format($daily['total'] ?? 0); ?></div></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="graph-panel h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" style="color: var(--cyan)"><i class="bi bi-broadcast me-2"></i><span id="chartLabel">Revenue Cycle Analysis</span></h5>
                    <div class="d-flex gap-2">
                        <button class="btn-reset" onclick="updateChart('yearly')">Yearly</button>
                        <button class="btn-reset" onclick="updateChart('weekly')">Reset</button>
                    </div>
                </div>
                <div style="height: 380px;"><canvas id="mainChart"></canvas></div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="ai-terminal mb-4">
                <h6 class="fw-800 small mb-3" style="color: var(--cyan); letter-spacing: 2px;"><i class="bi bi-robot me-2"></i>CORE AI ANALYTICS</h6>
                <div id="ai-log">
                    <div class="ai-msg">Neural link established. Monitoring pharmaceutical throughput...</div>
                </div>
                <div class="input-group">
                    <input type="text" id="ai-cmd" class="form-control ai-input shadow-none" placeholder="Query logistics (e.g. 'stock', 'users')">
                    <button class="btn btn-info rounded-3 ms-2" style="background: var(--cyan); border: none;" onclick="askAI()"><i class="bi bi-send-fill" style="color: #0F172A"></i></button>
                </div>
            </div>

            <div class="side-panel">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="customer_list.php" class="action-btn">
                            <i class="bi bi-people-fill mb-2"></i>Patients
                            <span class="btn-count-badge"><?php echo $total_customers; ?></span>
                        </a>
                    </div>
                    <div class="col-6"><a href="order.php" class="action-btn"><i class="bi bi-terminal mb-2"></i>Orders</a></div>
                    
                    <div class="col-6">
                        <a href="returns_report.php" class="action-btn">
                            <i class="bi bi-arrow-repeat mb-2"></i>Returns
                            <?php if($reversal_res['pending'] > 0): ?>
                                <span class="badge rounded-pill bg-danger ms-1" style="font-size: 0.6rem;"><?php echo $reversal_res['pending']; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                    
                    <div class="col-6">
                        <a href="messages.php" class="action-btn">
                            <i class="bi bi-chat-left-dots mb-2"></i>Inbox
                            <?php if($total_messages > 0): ?>
                                <span class="badge rounded-pill bg-info ms-1" style="font-size: 0.6rem;"><?php echo $total_messages; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="col-6"><a href="setting.php" class="action-btn"><i class="bi bi-shield-lock mb-2"></i>Safety</a></div>
                    <div class="col-6"><a href="../module/manage_inventory.php" class="action-btn"><i class="bi bi-box-seam mb-2"></i>Stock</a></div>
                    <div class="col-6"><a href="../module/add_medicine.php" class="action-btn"><i class="bi bi-plus-circle mb-2"></i>Add Item</a></div>
                    
                    <div class="col-12 mt-2">
                        <a href="../auth/logout.php" class="btn btn-outline-danger w-100 rounded-pill py-2 small fw-bold" style="border-width: 2px;">TERMINATE SESSION</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateClock() { document.getElementById('clock').innerText = new Date().toLocaleString(); }
    setInterval(updateClock, 1000); updateClock();

    // AI LOGIC
    function askAI() {
        const cmd = document.getElementById('ai-cmd');
        const log = document.getElementById('ai-log');
        if(!cmd.value) return;

        log.innerHTML += `<div style="color:white; margin-bottom:5px; font-size: 0.75rem; opacity: 0.6;">> ${cmd.value}</div>`;
        let res = "Analyzing database protocols...";
        
        const val = cmd.value.toLowerCase();
        if(val.includes('stock')) res = "Scan complete: <?php echo $low_stock; ?> items require immediate re-stocking protocol.";
        if(val.includes('revenue')) res = "Profitability matrix stable. Weekly velocity at ₹<?php echo number_format($weekly['total']); ?>.";
        if(val.includes('status')) res = "All systems operational. Security firewall at 100%.";
        if(val.includes('user') || val.includes('patient') || val.includes('customer')) res = "Database fetch complete: <?php echo $total_customers; ?> active patients registered.";

        setTimeout(() => {
            log.innerHTML += `<div class="ai-msg">${res}</div>`;
            log.scrollTop = log.scrollHeight;
        }, 600);
        cmd.value = '';
    }

    // CHART ENGINE
    const ctx = document.getElementById('mainChart').getContext('2d');
    let myChart;
    const dataSets = {
        weekly: { labels: <?php echo json_encode($w_labels); ?>, data: <?php echo json_encode($w_data); ?>, title: "7-Day Velocity Matrix" },
        monthly: { labels: ["Cycle Range"], data: [<?php echo $monthly['total'] ?? 0; ?>], title: "Monthly Throughput" },
        daily: { labels: ["Session Node"], data: [<?php echo $daily['total'] ?? 0; ?>], title: "Real-time Pulse" },
        yearly: { labels: <?php echo json_encode($y_labels); ?>, data: <?php echo json_encode($y_data); ?>, title: "Long-term Economic Growth (2024-2026)" }
    };

    function renderChart(scope) {
        if (myChart) myChart.destroy();
        document.getElementById('chartLabel').innerText = dataSets[scope].title;
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(45, 212, 191, 0.4)');
        gradient.addColorStop(1, 'rgba(45, 212, 191, 0)');

        myChart = new Chart(ctx, {
            type: scope === 'yearly' ? 'bar' : 'line',
            data: {
                labels: dataSets[scope].labels,
                datasets: [{
                    label: 'Credits (₹)',
                    data: dataSets[scope].data,
                    borderColor: '#2DD4BF',
                    backgroundColor: scope === 'yearly' ? '#2DD4BF' : gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2DD4BF',
                    pointRadius: scope === 'yearly' ? 0 : 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' } },
                    x: { grid: { display: false }, ticks: { color: '#94A3B8' } }
                }
            }
        });
    }
    function updateChart(s) { renderChart(s); }
    renderChart('weekly');
</script>
</body>
</html>