<?php
session_start();

// Gatekeeper: Ensure only Admins (Role 1) access this file
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

include('../config/db_connect.php'); 

// Logic for Adding Supplier (Logic Preserved)
if(isset($_POST['add_sup'])) {
    $n = mysqli_real_escape_string($conn, $_POST['s_name']);
    $c = mysqli_real_escape_string($conn, $_POST['contact']);
    $q = "INSERT INTO suppliers (s_name, contact) VALUES ('$n', '$c')";
    if(mysqli_query($conn, $q)) {
        header("Location: supplier.php?msg=success");
        exit();
    }
}

// Logic for Deleting Supplier (Logic Preserved)
if(isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM suppliers WHERE id='$id'");
    header("Location: supplier.php?msg=deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Supplier Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-bg: #0b0c10;
            --rose-gold: #c5a1a1;
            --card-bg: #111216;
            --glass-border: rgba(197, 161, 161, 0.15);
            --danger-red: #EE5D50;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--mims-bg); 
            color: #ffffff; 
            min-height: 100vh;
            background-image: radial-gradient(circle at 10% 20%, rgba(197, 161, 161, 0.03) 0%, transparent 40%);
        }
        
        .supplier-card { 
            background: var(--card-bg); 
            border-radius: 35px; 
            border: 1px solid var(--glass-border); 
            box-shadow: 0 30px 60px rgba(0,0,0,0.4); 
            overflow: hidden; 
        }

        .header-gradient {
            background: linear-gradient(135deg, #16171a 0%, #0b0c10 100%);
            color: white;
            padding: 45px 30px;
            border-bottom: 1px solid var(--glass-border);
            text-align: center;
        }

        .form-label-custom {
            font-size: 0.65rem;
            font-weight: 800;
            color: var(--rose-gold);
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 12px;
            display: block;
        }

        .form-control {
            background: rgba(255,255,255,0.02);
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            padding: 15px;
            font-weight: 600;
            color: white;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            background: rgba(255,255,255,0.05);
            border-color: var(--rose-gold);
            box-shadow: 0 0 20px rgba(197, 161, 161, 0.1);
            color: white;
        }

        .table thead th {
            color: var(--rose-gold);
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
            border-bottom: 1px solid var(--glass-border);
            padding: 25px 20px;
            background: rgba(255,255,255,0.01);
        }

        .table tbody td {
            padding: 22px 20px;
            vertical-align: middle;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            color: #e0e0e0;
        }

        .avatar-box {
            width: 50px; height: 50px;
            background: rgba(197, 161, 161, 0.05);
            color: var(--rose-gold);
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            display: flex; align-items: center; justify-content: center;
            border-radius: 16px;
            border: 1px solid var(--glass-border);
            font-size: 1.2rem;
        }

        .btn-delete {
            width: 42px; height: 42px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(238, 93, 80, 0.05);
            color: var(--danger-red);
            transition: 0.3s;
            border: 1px solid rgba(238, 93, 80, 0.15);
        }

        .btn-delete:hover {
            background: var(--danger-red);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(238, 93, 80, 0.3);
        }

        .btn-back {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            color: white;
            border-radius: 18px;
            padding: 12px 25px;
            font-weight: 700;
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
        }
        .btn-back:hover { border-color: var(--rose-gold); color: var(--rose-gold); background: rgba(197, 161, 161, 0.05); }

        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="py-5">

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-5 px-3">
        <div class="animate__animated animate__fadeIn">
            <h6 class="text-uppercase fw-800 mb-1" style="color: var(--rose-gold); letter-spacing: 4px; font-size: 0.7rem;">Procurement Network</h6>
            <h2 class="fw-800 mb-0" style="letter-spacing: -1.5px;">Supplier Registry</h2>
        </div>
        <a href="../admin/dashboard.php" class="btn btn-back animate__animated animate__fadeIn">
            <i class="bi bi-cpu me-2"></i> Command Center
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert animate__animated animate__slideInDown border-0 rounded-4 shadow-lg mb-5 p-4 d-flex align-items-center" style="background: rgba(197, 161, 161, 0.08); border: 1px solid var(--glass-border) !important; backdrop-filter: blur(10px);">
            <i class="bi bi-hdd-network-fill fs-3 me-3" style="color: var(--rose-gold);"></i>
            <div>
                <strong class="d-block text-white">Database Synchronized</strong>
                <span class="small opacity-75" style="color: var(--rose-gold);">
                    <?php echo ($_GET['msg'] == 'success') ? 'New logistics partner registered successfully.' : 'Partner entity purged from registry.'; ?>
                </span>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-5">
        <div class="col-lg-4">
            <div class="supplier-card animate__animated animate__fadeInLeft">
                <div class="header-gradient">
                    <div class="rounded-circle d-inline-flex p-3 mb-3" style="background: rgba(197, 161, 161, 0.05); border: 1px solid var(--glass-border);">
                        <i class="bi bi-building-up fs-2" style="color: var(--rose-gold);"></i>
                    </div>
                    <h4 class="fw-800 mb-1">Onboard Partner</h4>
                    <p class="small text-white-50 text-uppercase fw-bold mono" style="letter-spacing: 1px; font-size: 0.6rem;">Initialize Logistics Channel</p>
                </div>
                <div class="p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label-custom">Organization Name</label>
                            <input type="text" name="s_name" class="form-control" placeholder="e.g. PharmaCorp Global" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label-custom">Direct Point of Contact</label>
                            <input type="text" name="contact" class="form-control mono" placeholder="Secure Email or Line" required style="font-size: 0.85rem;">
                        </div>
                        <button type="submit" name="add_sup" class="btn w-100 py-3 fw-800 rounded-4 shadow" style="background: var(--rose-gold); color: #000; letter-spacing: 1px;">
                            COMMENCE PARTNERSHIP <i class="bi bi-chevron-right ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="supplier-card animate__animated animate__fadeInRight h-100">
                <div class="p-4 border-bottom d-flex justify-content-between align-items-center" style="border-color: var(--glass-border) !important;">
                    <h5 class="mb-0 fw-800"><i class="bi bi-shield-lock-fill me-2" style="color: var(--rose-gold);"></i>Verified Distributors</h5>
                    <span class="badge rounded-pill mono" style="background: rgba(197, 161, 161, 0.1); color: var(--rose-gold); border: 1px solid var(--glass-border);">SECURE_LIST</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Entity / ID</th>
                                <th>Logistics Contact</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY s_name ASC");
                            if(mysqli_num_rows($res) > 0) { 
                                while($row = mysqli_fetch_assoc($res)) {
                                    $initial = strtoupper(substr($row['s_name'], 0, 1));
                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-box me-3"><?php echo $initial; ?></div>
                                            <div>
                                                <div class="fw-800 text-white"><?php echo htmlspecialchars($row['s_name']); ?></div>
                                                <div class="text-white-50 mono" style="font-size: 0.65rem;">ID: SUP-<?php echo str_pad($row['id'], 4, "0", STR_PAD_LEFT); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold opacity-75 mono">
                                            <i class="bi bi-broadcast me-2" style="color: var(--rose-gold);"></i> 
                                            <?php echo htmlspecialchars($row['contact']); ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn-delete ms-auto" title="Decommission Partner">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center py-5'>
                                        <i class='bi bi-building-slash display-4 opacity-10 d-block mb-3'></i>
                                        <span class='text-white-50 fw-bold mono' style='font-size: 0.8rem;'>ERR: NO_PARTNERS_REGISTERED</span>
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(id) {
        if(confirm("PROTOCOL ALERT: Are you sure you wish to terminate this distribution partnership and purge the entity from your registry?")) {
            window.location.href = 'supplier.php?delete_id=' + id;
        }
    }
</script>
</body>
</html>