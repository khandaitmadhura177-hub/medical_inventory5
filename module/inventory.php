<?php
session_start();
include('../config/db_connect.php');

// Security Check
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 1);

// Search & Filter Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Dynamic Category Fetching
$cat_query = "SELECT category_name FROM categories ORDER BY category_name ASC";
$cat_result = mysqli_query($conn, $cat_query);

// Main Inventory Query
$query = "SELECT * FROM medicines WHERE 1=1";
if ($search != '') { $query .= " AND m_name LIKE '%$search%'"; }
if ($cat_filter != '') { $query .= " AND category = '$cat_filter'"; }
$query .= " ORDER BY quantity DESC, m_name ASC"; 
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Dispensary Grid</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700&display=swap');
        
        :root { 
            --bg: #0b0c10; 
            --panel: #16181d; 
            --accent: #10b981; 
        }

        body { 
            background-color: var(--bg); 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
        }

        /* SEARCH BAR FIX: Ensuring typed text is visible (Black) */
        .search-input-field {
            background-color: #ffffff !important;
            color: #000000 !important;
            font-weight: 600;
            border: 2px solid transparent;
        }
        .search-input-field:focus {
            border-color: var(--accent);
            box-shadow: none;
        }

        .dispensary-nav {
            background: var(--panel);
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            margin-bottom: 40px;
        }

        /* Medicine Card Design */
        .med-card {
            background: #FFFFFF !important;
            border-radius: 28px;
            padding: 25px;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: none;
            position: relative;
        }

        .med-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .med-card h5 { color: #0f172a !important; font-weight: 800; font-size: 1.15rem; }
        .med-card p { color: #64748b !important; font-weight: 600; }

        .med-image-container {
            height: 160px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            background: #f8fafc;
            border-radius: 20px;
            overflow: hidden;
        }

        .med-image-container img {
            max-height: 85%;
            max-width: 85%;
            object-fit: contain;
        }

        .price-tag { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 15px; }
        .btn-cart { background: #0f172a !important; color: white !important; border-radius: 12px; font-weight: 700; border: none; }
        .btn-purchase { background: var(--accent); color: white !important; border-radius: 12px; font-weight: 700; border: none; }

        /* Stock Status */
        .out-of-stock { filter: grayscale(1); opacity: 0.7; }
        .stock-label {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 5px 12px;
            z-index: 5;
        }
    </style>
</head>
<body>

<nav class="dispensary-nav">
    <div class="container d-flex justify-content-between align-items-center">
        <h3 class="m-0" style="font-family:'Outfit';">MIMS <span style="color:var(--accent)">DISPENSARY</span></h3>
        <div class="d-flex gap-4 align-items-center">
            <a href="../customer/cart.php" class="text-white text-decoration-none position-relative me-2">
                <i class="bi bi-cart3 fs-4"></i>
                <?php 
                $cart_count = 0;
                if(isset($_SESSION['cart'])) {
                    foreach($_SESSION['cart'] as $item) { $cart_count += $item['quantity']; }
                }
                if($cart_count > 0): 
                ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                    <?php echo $cart_count; ?>
                </span>
                <?php endif; ?>
            </a>

            <a href="../customer/my_orders.php" class="text-white text-decoration-none fw-600 me-2">
                <i class="bi bi-bag-check me-1"></i> My Orders
            </a>

            <a href="<?php echo $is_admin ? '../admin/dashboard.php' : '../customer/dashboard.php'; ?>" class="btn btn-sm btn-outline-light rounded-pill px-4 fw-600">
                <i class="bi bi-grid-fill me-2"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    <div class="row g-3 mb-5 animate__animated animate__fadeIn">
        <div class="col-md-7">
            <form method="GET">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control search-input-field py-2" placeholder="Search for medicines..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </form>
        </div>
        <div class="col-md-5">
            <form method="GET" class="d-flex gap-2">
                <select name="category" class="form-select bg-dark text-white border-secondary">
                    <option value="">All Categories</option>
                    <?php 
                    while($c = mysqli_fetch_assoc($cat_result)) {
                        $selected = ($cat_filter == $c['category_name']) ? 'selected' : '';
                        echo "<option value='".$c['category_name']."' $selected>".$c['category_name']."</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-success px-4 fw-bold">FILTER</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): 
                $is_out = ($row['quantity'] <= 0);
            ?>
            <div class="col-lg-3 col-md-6 animate__animated animate__fadeInUp">
                <div class="med-card <?php echo $is_out ? 'out-of-stock' : ''; ?>">
                    <?php if($is_out): ?>
                        <span class="badge bg-secondary stock-label rounded-pill">OUT OF STOCK</span>
                    <?php endif; ?>

                    <div>
                        <div class="med-image-container">
                            <img src="../assets/image/medicine/<?php echo basename($row['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['m_name']); ?>"
                                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/883/883356.png';">
                        </div>
                        <span class="badge bg-light text-secondary mb-2"><?php echo htmlspecialchars($row['category']); ?></span>
                        <h5 class="mb-1"><?php echo htmlspecialchars($row['m_name']); ?></h5>
                        <p class="small mb-0">Units Available: <strong><?php echo $row['quantity']; ?></strong></p>
                    </div>

                    <div class="mt-4">
                        <div class="price-tag">₹<?php echo number_format($row['price'], 2); ?></div>
                        <div class="d-grid gap-2">
                            <a href="../customer/add_to_cart.php?id=<?php echo $row['id']; ?>&action=add" 
                               class="btn btn-cart py-2 <?php echo $is_out ? 'disabled' : ''; ?>">
                               <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </a>
                            <a href="../customer/checkout.php?id=<?php echo $row['id']; ?>&direct=1" 
                               class="btn btn-purchase py-2 <?php echo $is_out ? 'disabled' : ''; ?>">
                               Quick Buy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-capsule-pill display-1 opacity-25"></i>
                <h4 class="mt-4 opacity-50">No medicines found in this sector.</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>