<?php
session_start();
include('../config/db_connect.php');

// --- NEURAL GUARD: FIXES BACK-KEY SECURITY ---
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// SECURITY: Admin Only (Role 1)
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch customers and join with order count
$query = "SELECT u.*, COUNT(o.id) as order_count 
          FROM users u 
          LEFT JOIN orders o ON u.id = o.user_id 
          WHERE u.role = 0 
          GROUP BY u.id 
          ORDER BY u.id DESC";
$result = mysqli_query($conn, $query);

$count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 0");
$count_data = mysqli_fetch_assoc($count_res);
$total_customers = $count_data['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Customer Directory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800;900&display=swap');
        
        :root { 
            --bg: #030712; 
            --cyan: #2DD4BF; 
            --card-bg: #0b0e14; 
            --border: rgba(45, 212, 191, 0.15); 
            --text-gray: #94A3B8;
        }

        body { 
            background: var(--bg); 
            color: #F8FAFC; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        .admin-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 30px; 
            padding: 25px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            overflow: hidden;
        }

        /* Neural Alerts Style */
        .neural-alert {
            animation: slideDown 0.5s ease forwards;
            border-radius: 15px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .btn-neural-back {
            background: rgba(45, 212, 191, 0.05);
            border: 1px solid var(--border);
            color: var(--cyan);
            border-radius: 12px;
            padding: 8px 18px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.8rem;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
        }
        .btn-neural-back:hover {
            background: var(--cyan);
            color: #030712;
        }

        .search-container {
            background: #000;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
        }
        .search-input {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            margin-left: 10px;
            outline: none;
        }

        .table { 
            color: #F8FAFC !important; 
            vertical-align: middle; 
            margin-bottom: 0;
            background-color: transparent !important;
        }
        
        .table thead th {
            background-color: transparent !important;
            border-bottom: 2px solid var(--cyan) !important;
            color: var(--cyan) !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 800;
            padding: 15px;
            font-family: 'Outfit';
        }

        .table tbody tr { 
            border-bottom: 1px solid rgba(255,255,255,0.05) !important; 
            transition: 0.2s;
            background-color: transparent !important;
        }
        
        .table tbody tr td {
            background-color: transparent !important;
            color: #F8FAFC !important;
            border: none;
        }

        .table tbody tr:hover { 
            background: rgba(45, 212, 191, 0.05) !important; 
        }

        .vip-badge {
            background: rgba(45, 212, 191, 0.15);
            color: var(--cyan);
            font-size: 0.65rem;
            padding: 5px 10px;
            border-radius: 6px;
            font-weight: 800;
            border: 1px solid var(--cyan);
        }

        .order-circle {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: var(--cyan);
            color: #000;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; font-family: 'Outfit';
            margin: auto;
        }

        .action-link {
            color: var(--text-gray);
            font-size: 1.1rem;
            transition: 0.3s;
            margin-left: 10px;
        }
        .action-link:hover { color: var(--cyan); }
        .delete-link:hover { color: #ef4444; }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .patient-row { animation: slideUp 0.3s ease forwards; }
    </style>
</head>
<body>

<div class="container py-5">
    
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="alert neural-alert mb-4 py-3 text-center" style="background: rgba(45, 212, 191, 0.1); color: var(--cyan);">
            <i class="bi bi-shield-check me-2"></i> NODE SUCCESSFULLY DE-ACTIVATED AND PURGED
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error']) && $_GET['error'] == 'self_delete'): ?>
        <div class="alert neural-alert mb-4 py-3 text-center" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
            <i class="bi bi-exclamation-octagon me-2"></i> CRITICAL ERROR: ADMIN CORE CANNOT BE SELF-TERMINATED
        </div>
    <?php endif; ?>

    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <a href="dashboard.php" class="btn-neural-back mb-3">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
            <h1 class="display-6 fw-900" style="font-family: 'Outfit';">Customer <span style="color: var(--cyan);">List</span></h1>
            <p class="text-muted small">Managing all registered medical profiles</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="d-inline-block p-3 rounded-4 border text-start" style="background: var(--card-bg); border-color: var(--border) !important; min-width: 200px;">
                <div class="text-muted small fw-bold text-uppercase">Total Customers</div>
                <div class="h3 fw-900 m-0 text-cyan" style="font-family: 'Outfit';"><?php echo $total_customers; ?></div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-3">
        <div class="col-md-7">
            <div class="search-container">
                <i class="bi bi-search text-cyan"></i>
                <input type="text" id="userInput" class="search-input" placeholder="Find customer by name, email or ID..." onkeyup="searchTable()">
            </div>
        </div>
        <div class="col-md-5 text-md-end">
            <button onclick="window.print()" class="btn-neural-back py-2">
                <i class="bi bi-printer"></i> Print Records
            </button>
        </div>
    </div>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table" id="customerTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Status</th>
                        <th>Full Name</th>
                        <th>Contact Details</th>
                        <th class="text-center">Orders</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="patient-row">
                            <td class="fw-bold opacity-75 searchable-id">#<?php echo $row['id']; ?></td>
                            <td>
                                <?php if($row['order_count'] > 5): ?>
                                    <span class="vip-badge">VIP CUSTOMER</span>
                                <?php else: ?>
                                    <span class="text-muted small fw-bold">REGULAR</span>
                                <?php endif; ?>
                            </td>
                            <td class="searchable-name">
                                <div class="fw-bold text-white"><?php echo htmlspecialchars($row['username']); ?></div>
                                <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($row['address'] ?? 'No Address Provided'); ?></div>
                            </td>
                            <td class="searchable-email">
                                <div class="text-white small"><?php echo htmlspecialchars($row['email']); ?></div>
                                <div class="fw-bold small" style="color: var(--cyan);"><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></div>
                            </td>
                            <td>
                                <div class="order-circle"><?php echo $row['order_count']; ?></div>
                            </td>
                            <td class="text-end">
                                <a href="view_customer.php?id=<?php echo $row['id']; ?>" class="action-link" title="View Details"><i class="bi bi-eye"></i></a>
                                <a href="delete_user.php?id=<?php echo $row['id']; ?>" class="action-link delete-link" onclick="return confirm('Delete this customer account permanently?')" title="Delete Account"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function searchTable() {
    const input = document.getElementById('userInput').value.toLowerCase();
    const rows = document.querySelectorAll('.patient-row');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}
</script>

</body>
</html>