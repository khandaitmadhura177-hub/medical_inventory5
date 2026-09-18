<?php
session_start();
include('../config/db_connect.php');

// Admin Security Gate
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?msg=Access Denied.");
    exit();
}

// 1. HANDLE ADD CATEGORY
if (isset($_POST['add_category'])) {
    $cat_name = mysqli_real_escape_string($conn, trim($_POST['category_name']));
    if (!empty($cat_name)) {
        $check = mysqli_query($conn, "SELECT * FROM categories WHERE category_name = '$cat_name'");
        if (mysqli_num_rows($check) == 0) {
            mysqli_query($conn, "INSERT INTO categories (category_name) VALUES ('$cat_name')");
            header("Location: manage_categories.php?status=success&msg=Classification '" . $cat_name . "' Registered");
        } else {
            header("Location: manage_categories.php?status=error&msg=Classification already exists in database");
        }
    }
    exit();
}

// 2. HANDLE DELETE CATEGORY
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Optional: You could update medicines set category = 'Uncategorized' here
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    header("Location: manage_categories.php?status=success&msg=Classification Purged");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Manage Classifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root { 
            --bg: #0b0c10; 
            --rose-gold: #b76e79; 
            --card-bg: #14161a; 
            --accent-cyan: #00e5ff; 
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            color: #e2e8f0; 
            padding: 60px 0; 
        }

        .glass-card { 
            background: var(--card-bg); 
            border: 1px solid rgba(183, 110, 121, 0.1); 
            border-radius: 30px; 
            padding: 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .form-control { 
            background: #1f2024; 
            border: 1px solid rgba(255,255,255,0.05); 
            border-radius: 15px; 
            color: white; 
            padding: 14px; 
            transition: 0.3s;
        }

        .form-control:focus { 
            background: #1f2024; 
            color: white; 
            border-color: var(--accent-cyan); 
            box-shadow: 0 0 15px rgba(0, 229, 255, 0.1); 
        }

        .table { --bs-table-bg: transparent; color: #cbd5e1; }
        
        .btn-add { 
            background: var(--rose-gold); 
            border: none; 
            border-radius: 15px; 
            font-weight: 800; 
            color: white; 
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-add:hover { 
            background: #d48c96; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(183, 110, 121, 0.3);
        }

        .category-badge {
            background: rgba(0, 229, 255, 0.05);
            color: var(--accent-cyan);
            border: 1px solid rgba(0, 229, 255, 0.2);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>

<div class="container animate__animated animate__fadeIn">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div>
                    <h1 class="fw-800 m-0" style="letter-spacing: -2px;">System <span style="color: var(--rose-gold);">Taxonomy</span></h1>
                    <p class="text-white-50 mb-0">Define and regulate medical inventory classifications.</p>
                </div>
                <a href="inventory.php" class="btn btn-outline-light rounded-pill px-4 fw-bold small">
                    <i class="bi bi-arrow-left me-2"></i>Inventory
                </a>
            </div>

            <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-custom animate__animated animate__shakeX" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; color: var(--accent-cyan);">
                    <div class="d-flex align-items-center">
                        <i class="bi bi- megaphone-fill me-3 fs-4"></i>
                        <span class="fw-bold small"><?php echo htmlspecialchars($_GET['msg']); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="glass-card mb-5">
                <form method="POST" class="row g-4">
                    <div class="col-md-8">
                        <label class="form-label small fw-bold text-uppercase opacity-50 ms-2">Classification Label</label>
                        <input type="text" name="category_name" class="form-control" placeholder="e.g. INFECTIOUS_DISEASE" required>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" name="add_category" class="btn btn-add w-100 py-3">REGISTER_NEW</button>
                    </div>
                </form>
            </div>

            <div class="glass-card">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-white-50 small text-uppercase">
                                <th class="ps-3" style="letter-spacing: 2px;">Registry_ID</th>
                                <th style="letter-spacing: 2px;">Classification_Label</th>
                                <th class="text-center" style="letter-spacing: 2px;">Active_SKUs</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // JOIN query to count how many medicines are in each category
                            $query = "SELECT c.*, COUNT(m.id) as med_count 
                                      FROM categories c 
                                      LEFT JOIN medicines m ON c.category_name = m.category 
                                      GROUP BY c.id 
                                      ORDER BY c.category_name ASC";
                            $list = mysqli_query($conn, $query);
                            
                            while($row = mysqli_fetch_assoc($list)):
                            ?>
                            <tr class="animate__animated animate__fadeIn">
                                <td class="ps-3"><span class="text-white-50 font-monospace">#0<?php echo $row['id']; ?></span></td>
                                <td>
                                    <div class="fw-800 fs-5"><?php echo htmlspecialchars($row['category_name']); ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge category-badge rounded-pill px-3">
                                        <?php echo $row['med_count']; ?> ITEMS
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="manage_categories.php?delete=<?php echo $row['id']; ?>" 
                                       class="btn btn-link text-danger p-0" 
                                       onclick="return confirm('WARNING: Deleting this classification will leave <?php echo $row['med_count']; ?> items uncategorized. Continue?')">
                                        <i class="bi bi-trash3-fill fs-5"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>