<?php
session_start();

// 1. GATEKEEPER: Ensure only Admins (Role 1) access this file
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

include('../config/db_connect.php');

// 2. DELETE LOGIC
if(isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id']; 
    
    // Optional: Fetch image name to delete file from server
    $img_res = mysqli_query($conn, "SELECT image FROM medicines WHERE id=$id");
    $img_data = mysqli_fetch_assoc($img_res);
    if($img_data['image'] && $img_data['image'] != 'default.png') {
        @unlink("../assets/image/medicine/" . $img_data['image']);
    }

    mysqli_query($conn, "DELETE FROM medicines WHERE id='$id'");
    header("Location: manage_inventory.php?msg=deleted");
    exit();
}

$search_query = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Global Inventory Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800;900&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #030712; 
            --panel: #0b0e14; 
            --text: #F8FAFC; 
            --border: rgba(45, 212, 191, 0.15); 
            --cyan: #2DD4BF; 
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg);
            min-height: 100vh;
            color: var(--text);
            overflow-x: hidden;
        }

        /* --- GLASS CARD & TABLE --- */
        .glass-card {
            background: var(--panel);
            border-radius: 25px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .table { 
            background: transparent !important; 
            color: var(--text) !important;
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .table thead th {
            color: var(--cyan);
            text-transform: uppercase;
            font-family: 'Outfit';
            font-size: 0.75rem;
            font-weight: 800;
            padding: 20px 15px;
            background: rgba(45, 212, 191, 0.05) !important; 
            border: none;
            letter-spacing: 1px;
        }

        .table tbody tr {
            background: rgba(255,255,255,0.02) !important;
            transition: 0.3s ease;
        }

        .table tbody tr:hover {
            background: rgba(45, 212, 191, 0.08) !important;
            transform: translateX(8px);
        }

        .table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            border: none !important;
            font-weight: 600;
        }

        .medicine-name { 
            color: var(--text) !important; 
            font-weight: 800 !important; 
            font-size: 1.05rem !important; 
            display: block;
            line-height: 1.2;
        }
        
        .sku-label {
            font-family: 'JetBrains Mono', monospace;
            color: var(--cyan) !important; 
            opacity: 0.6;
            font-size: 0.7rem;
        }

        .img-inventory {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: #161b22;
        }
        
        /* --- ACTION BUTTONS --- */
        .btn-action {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            border: 1px solid var(--border);
            color: var(--text);
            background: rgba(255,255,255,0.05);
        }

        .btn-action:hover {
            background: var(--cyan);
            color: var(--bg);
            transform: translateY(-2px);
        }

        .btn-action.text-danger:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }

        /* --- SEARCH & INPUTS --- */
        .search-box {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 12px 15px 12px 45px;
            width: 100%;
            color: white;
            font-weight: 600;
        }
        
        .search-box:focus {
            outline: none;
            border-color: var(--cyan);
            background: rgba(255,255,255,0.08);
        }

        .badge-stock {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 0.65rem;
            text-transform: uppercase;
        }
        .low-stock { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
        .good-stock { background: rgba(45, 212, 191, 0.1); color: var(--cyan); border: 1px solid var(--border); }
        
        .btn-cyan { background: var(--cyan); color: var(--bg); font-weight: 800; border-radius: 12px; }
        .btn-cyan:hover { opacity: 0.9; transform: translateY(-2px); }
    </style>
</head>
<body class="py-5">

<div class="container animate__animated animate__fadeIn">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5 gap-3">
        <div>
            <h1 class="fw-900 text-white mb-1" style="font-family: 'Outfit'; letter-spacing: -1px;">Inventory <span style="color: var(--cyan);">Nexus</span></h1>
            <p class="opacity-50 mb-0"><i class="bi bi-shield-lock-fill me-2 text-cyan"></i>Verified Administrative Access Level 1</p>
        </div>
        <div class="d-flex gap-2">
            <a href="../admin/dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold btn-sm">DASHBOARD</a>
            <a href="add_medicine.php" class="btn btn-cyan rounded-pill px-4 fw-bold shadow-sm btn-sm">
                <i class="bi bi-plus-lg me-1"></i> NEW ENTRY
            </a>
        </div>
    </div>

    <div class="glass-card">
        <div class="p-4 d-flex justify-content-between align-items-center border-bottom border-secondary border-opacity-10">
            <div class="position-relative w-50">
                <i class="bi bi-search position-absolute text-cyan" style="left: 18px; top: 14px;"></i>
                <input type="text" id="searchInput" class="search-box" 
                       placeholder="Filter by name, category, or SKU..."
                       value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            <div class="d-none d-md-block">
                <span class="badge rounded-pill bg-dark border border-secondary px-3 py-2" style="font-size: 0.65rem; letter-spacing: 1px; font-family: 'JetBrains Mono';">
                    <span class="text-cyan">●</span> LIVE_SYNC_ACTIVE
                </span>
            </div>
        </div>

        <div class="table-responsive p-3">
            <table class="table align-middle" id="invTable">
                <thead>
                    <tr>
                        <th class="ps-4">Medicine Specifications</th>
                        <th>Classification</th>
                        <th>Stock Status</th>
                        <th>Price (Unit)</th>
                        <th>Expiry Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM medicines ORDER BY m_name ASC";
                    $res = mysqli_query($conn, $sql);
                    
                    if(mysqli_num_rows($res) > 0) {
                        while($row = mysqli_fetch_assoc($res)) {
                            $stock_val = (int)$row['quantity'];
                            $stock_class = ($stock_val < 10) ? 'low-stock' : 'good-stock';
                            $img_filename = !empty($row['image']) ? $row['image'] : 'default_medicine.png';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/image/medicine/<?php echo $img_filename; ?>" class="img-inventory me-3">
                                        <div>
                                            <span class="medicine-name"><?php echo htmlspecialchars($row['m_name']); ?></span>
                                            <span class="sku-label">ID_REF: #<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-uppercase small fw-bold opacity-75"><?php echo htmlspecialchars($row['category'] ?: 'Unassigned'); ?></span>
                                </td>
                                <td>
                                    <span class="badge-stock <?php echo $stock_class; ?>">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                        <?php echo $stock_val; ?> in stock
                                    </span>
                                </td>
                                <td><span class="fw-800 text-cyan">₹<?php echo number_format($row['price'], 2); ?></span></td>
                                <td>
                                    <?php 
                                        $exp_date = strtotime($row['expiry_date']);
                                        $is_expired = $exp_date < time();
                                    ?>
                                    <span class="<?php echo $is_expired ? 'text-danger fw-bold' : 'opacity-75'; ?>">
                                        <?php echo date('M d, Y', $exp_date); ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href='edit_medicine.php?id=<?php echo $row['id']; ?>' class='btn-action' title="Edit">
                                            <i class='bi bi-pencil-square'></i>
                                        </a>
                                        <button onclick='confirmDelete(<?php echo $row['id']; ?>, "<?php echo addslashes($row['m_name']); ?>")' class='btn-action text-danger' title="Purge">
                                            <i class='bi bi-trash3'></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-5 opacity-50'>No medical assets found in local registry.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Real-time DOM Filtering (Logic preserved)
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#invTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    function confirmDelete(id, name) {
        if(confirm("CRITICAL ACTION: Are you sure you want to purge '" + name + "' from the system? This cannot be undone.")) {
            window.location.href = 'manage_inventory.php?delete_id=' + id;
        }
    }
</script>
</body>
</html>