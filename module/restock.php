<?php
session_start();

// 1. GATEKEEPER: Ensure only Admins (Role 1) access this file
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

include('../config/db_connect.php');

// 2. Logic to handle stock update (Logic preserved exactly as provided)
$message = "";
if(isset($_POST['add_stock'])) {
    $id = mysqli_real_escape_string($conn, $_POST['med_id']);
    $new_qty = (int)$_POST['qty_to_add'];
    
    // Increment existing quantity
    $sql = "UPDATE medicines SET quantity = quantity + $new_qty WHERE id='$id'";
    
    if(mysqli_query($conn, $sql)) {
        $message = "success";
    } else {
        $message = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Inventory Replenishment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-bg: #0b0c10;
            --emerald: #05CD99;
            --rose-gold: #c5a1a1;
            --card-bg: #111216;
            --glass-border: rgba(197, 161, 161, 0.15);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--mims-bg);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            background-image: radial-gradient(circle at top right, rgba(5, 205, 153, 0.03), transparent);
        }

        .restock-card {
            background: var(--card-bg);
            border-radius: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.5);
            overflow: hidden;
            border: 1px solid var(--glass-border);
        }

        .header-accent {
            background: linear-gradient(135deg, #16171a 0%, #0b0c10 100%);
            padding: 55px 30px;
            text-align: center;
            border-bottom: 1px solid var(--glass-border);
        }

        .icon-circle {
            width: 80px; height: 80px;
            background: rgba(5, 205, 153, 0.05);
            color: var(--emerald);
            border: 1px solid rgba(5, 205, 153, 0.2);
            border-radius: 24px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 25px; font-size: 32px;
        }

        .form-label-custom {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--rose-gold);
            font-weight: 800;
            margin-bottom: 12px;
            display: block;
        }

        .form-control, .form-select {
            background-color: rgba(255,255,255,0.02);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 15px 20px;
            font-weight: 600;
            color: white;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255,255,255,0.05);
            border-color: var(--emerald);
            box-shadow: 0 0 20px rgba(5, 205, 153, 0.15);
            color: white;
        }

        .btn-restock {
            background: var(--emerald);
            color: #0b0c10;
            border: none;
            border-radius: 16px;
            padding: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: 0.4s;
            font-size: 0.85rem;
        }

        .btn-restock:hover {
            background: #06e0a7;
            transform: translateY(-4px);
            box-shadow: 0 15px 30px rgba(5, 205, 153, 0.3);
        }

        .alert-custom {
            background: rgba(5, 205, 153, 0.1);
            border: 1px solid var(--emerald);
            color: var(--emerald);
            border-radius: 22px;
            backdrop-filter: blur(10px);
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
        
        option { background: #1a1c20; color: white; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            
            <?php if($message == "success"): ?>
                <div class="alert alert-custom animate__animated animate__fadeInDown d-flex align-items-center mb-4 p-4 shadow-lg">
                    <i class="bi bi-check2-circle fs-2 me-3"></i>
                    <div>
                        <strong class="d-block mb-1">Batch Processed</strong>
                        <span class="small opacity-75">Global inventory synchronized successfully.</span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="restock-card animate__animated animate__fadeInUp">
                <div class="header-accent">
                    <div class="icon-circle animate__animated animate__pulse animate__infinite">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                    <h3 class="fw-800 mb-1" style="letter-spacing: -1px;">Inventory Entry</h3>
                    <p class="small text-white-50 fw-bold text-uppercase" style="letter-spacing: 2.5px; font-size: 0.65rem;">Supply Chain Management</p>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label-custom">Select Medicine SKU</label>
                            <select name="med_id" class="form-select" required>
                                <option value="" disabled selected>Identify Item...</option>
                                <?php
                                $list = mysqli_query($conn, "SELECT id, m_name, quantity FROM medicines ORDER BY m_name ASC");
                                while($row = mysqli_fetch_assoc($list)) {
                                    $is_low = ($row['quantity'] < 10);
                                    $status = $is_low ? " [LOW: {$row['quantity']}]" : " [{$row['quantity']} Units]";
                                    echo "<option value='{$row['id']}' " . ($is_low ? 'style="color: #ff4b5c; font-weight: bold;"' : '') . ">" 
                                         . htmlspecialchars($row['m_name']) . " {$status}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="form-label-custom">Arriving Unit Count</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--glass-border); border-radius: 16px 0 0 16px;">
                                    <i class="bi bi-plus-square text-success"></i>
                                </span>
                                <input type="number" name="qty_to_add" class="form-control border-start-0 mono" 
                                       placeholder="Quantity..." required min="1"
                                       style="border-radius: 0 16px 16px 0;">
                            </div>
                        </div>

                        <button type="submit" name="add_stock" class="btn btn-restock w-100 mb-4 shadow">
                            Commit Batch Record <i class="bi bi-arrow-right-short ms-1"></i>
                        </button>

                        <div class="text-center">
                            <a href="../admin/dashboard.php" class="text-white-50 text-decoration-none small fw-bold opacity-75 hover-opacity-100" style="transition: 0.3s;">
                                <i class="bi bi-arrow-left-circle me-2"></i>Return to Hub
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 p-3 rounded-4 d-flex align-items-center opacity-50 border border-secondary" style="background: rgba(255,255,255,0.02); border-style: dashed !important;">
                <i class="bi bi-info-circle-fill me-3 fs-5" style="color: var(--rose-gold);"></i>
                <span class="small mono" style="font-size: 0.7rem;">SYSTEM_NOTICE: Inventory updates impact Financial Analytics curves in real-time.</span>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>