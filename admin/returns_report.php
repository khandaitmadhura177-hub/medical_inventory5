<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

/** * Query 1: Fetch PENDING requests */
$pending_query = "SELECT orders.*, users.username, medicines.m_name, medicines.image, medicines.category 
                  FROM orders 
                  LEFT JOIN users ON orders.user_id = users.id 
                  LEFT JOIN medicines ON orders.medicine_id = medicines.id 
                  WHERE orders.status = 'Return Requested' 
                  ORDER BY orders.order_date DESC";
$pending_res = mysqli_query($conn, $pending_query);

/** * Query 2: Fetch COMPLETED returns */
$query = "SELECT orders.*, users.username, medicines.m_name, medicines.image, medicines.category 
          FROM orders 
          LEFT JOIN users ON orders.user_id = users.id 
          LEFT JOIN medicines ON orders.medicine_id = medicines.id 
          WHERE orders.status = 'Returned'
          ORDER BY orders.order_date DESC";
$result = mysqli_query($conn, $query);

// Refund Sum Logic
$refund_query = mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE status = 'Returned'");
$refund_data = mysqli_fetch_assoc($refund_query);
$total_refunded = $refund_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Returns Intelligence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root {
            --bg-deep: #030712;
            --cyan: #2DD4BF;
            --card-bg: #0b0e14;
            --border: rgba(45, 212, 191, 0.3); /* Increased opacity for better box visibility */
        }

        body { 
            background: var(--bg-deep);
            background-image: radial-gradient(circle at 0% 0%, rgba(45, 212, 191, 0.05) 0%, transparent 50%);
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #ffffff; min-height: 100vh;
        }

        .header-banner { 
            background: var(--card-bg); 
            border-radius: 30px; padding: 45px; 
            border: 1px solid var(--border);
            margin-bottom: 35px;
        }

        .glass-card { 
            background: #000000; 
            border-radius: 28px; 
            border: 1px solid var(--border); 
            transition: 0.3s ease;
        }
        .glass-card:hover { border-color: var(--cyan); }

        /* VISIBILITY FIX: Ensuring all table text is pure white or high-contrast cyan */
        .table thead th { 
            background: #0b0e14 !important; border: none; font-weight: 800; 
            color: var(--cyan); text-transform: uppercase; font-size: 0.75rem; 
            letter-spacing: 2px; padding: 20px;
            border-bottom: 2px solid var(--border) !important;
        }
        .table { color: #ffffff !important; }
        .table td { 
            padding: 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            vertical-align: middle; 
            background: transparent !important;
            color: #ffffff !important; /* Forces text visibility */
        }
        
        /* Ensuring secondary text isn't too dark */
        .text-muted-custom { color: rgba(255, 255, 255, 0.7) !important; }

        .restored-badge { 
            background: rgba(45, 212, 191, 0.1); color: var(--cyan); 
            padding: 6px 14px; border-radius: 10px; font-size: 0.65rem; font-weight: 800; 
            border: 1px solid var(--cyan); letter-spacing: 1px;
        }

        .medicine-icon { 
            width: 48px; height: 48px; border-radius: 12px; 
            background: #0b0e14; 
            display: flex; align-items: center; justify-content: center; 
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .medicine-icon img { width: 100%; height: 100%; object-fit: cover; }

        .btn-terminal { 
            background: rgba(255, 255, 255, 0.05); color: white; 
            border: 1px solid var(--border); border-radius: 15px;
            transition: 0.3s; text-decoration: none; font-weight: 700;
            padding: 10px 20px;
        }
        .btn-terminal:hover { background: var(--cyan); color: #000; border-color: var(--cyan); }

        .btn-approve {
            background: var(--cyan); color: #000; font-weight: 900;
            border: none; border-radius: 12px; padding: 10px 20px; font-size: 0.75rem;
            transition: 0.3s; text-decoration: none; text-transform: uppercase;
        }
        .btn-approve:hover { background: white; transform: scale(1.05); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="header-banner d-flex justify-content-between align-items-center animate__animated animate__fadeIn">
        <div>
            <span class="badge mb-2 fw-bold" style="background: var(--cyan); color: #000;">FINANCIAL INTELLIGENCE</span>
            <h1 class="fw-900 mb-0 display-5" style="font-family: 'Outfit';">Returns Analytics</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-terminal"><i class="bi bi-cpu me-2"></i>Dashboard</a>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="stat-widget glass-card">
                <p class="text-white-50 fw-bold small mb-1">TOTAL REVENUE FOREGONE</p>
                <h2 class="fw-900 text-cyan">₹<?php echo number_format($total_refunded, 2); ?></h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-widget glass-card">
                <p class="text-white-50 fw-bold small mb-1">UNITS RESTORED</p>
                <h2 class="fw-900 text-white"><?php echo mysqli_num_rows($result); ?></h2>
            </div>
        </div>
    </div>

    <div class="glass-card p-4 animate__animated animate__fadeInUp">
        <h5 class="fw-900 mb-4 text-white" style="font-family: 'Outfit';">
            <i class="bi bi-clock-history text-cyan me-2"></i>Reversal Archive
        </h5>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>LOG ID</th>
                        <th>PRODUCT CONTEXT</th>
                        <th>DELTA</th>
                        <th>CLIENT</th>
                        <th>FISCAL IMPACT</th>
                        <th>STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td class="fw-bold text-muted-custom">#ORD-<?php echo $row['id']; ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="medicine-icon">
                                    <?php if(!empty($row['image'])): ?>
                                        <img src="../assets/image/medicine/<?php echo $row['image']; ?>" alt="Medicine">
                                    <?php else: ?>
                                        <i class="bi bi-capsule-pill text-cyan"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="fw-bold text-white"><?php echo htmlspecialchars($row['m_name']); ?></div>
                            </div>
                        </td>
                        <td><div class="text-cyan fw-900">+ <?php echo $row['quantity']; ?></div></td>
                        <td class="text-white"><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><span class="fw-900 text-danger">₹<?php echo number_format($row['total_amount'], 2); ?></span></td>
                        <td><span class="restored-badge">RESTORED</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>