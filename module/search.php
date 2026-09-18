<?php
session_start();
include('../config/db_connect.php');

// Security: Ensure user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$query = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$is_admin = ($_SESSION['role'] == 1);

// Refined Search Query: Matches name or category (Logic Preserved)
$sql = "SELECT * FROM medicines 
        WHERE m_name LIKE '%$query%' 
        OR category LIKE '%$query%' 
        ORDER BY m_name ASC";
$result = mysqli_query($conn, $sql);
$count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Global Search</title>
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
        }

        body { 
            background-color: var(--mims-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #ffffff; 
            background-image: radial-gradient(circle at top left, rgba(197, 161, 161, 0.02), transparent);
        }
        
        .search-header {
            background: rgba(11, 12, 16, 0.85);
            backdrop-filter: blur(20px);
            padding: 25px 0;
            border-bottom: 1px solid var(--glass-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .search-input-group {
            background: rgba(255,255,255,0.02);
            border-radius: 20px;
            padding: 4px 12px;
            display: flex;
            align-items: center;
            border: 1px solid var(--glass-border);
            transition: 0.4s;
        }

        .search-input-group:focus-within {
            border-color: var(--rose-gold);
            background: rgba(255,255,255,0.05);
            box-shadow: 0 0 25px rgba(197, 161, 161, 0.1);
        }

        .search-input-group input {
            background: transparent;
            border: none;
            color: white;
            box-shadow: none !important;
            padding: 12px;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .result-card {
            border: 1px solid var(--glass-border);
            border-radius: 35px;
            background: var(--card-bg);
            transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .result-card:hover {
            transform: translateY(-12px);
            border-color: var(--rose-gold);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        .category-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            color: var(--rose-gold);
            margin-bottom: 15px;
            display: block;
            opacity: 0.8;
        }

        .price-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .stock-pill {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stock-low { background: rgba(255, 75, 92, 0.1); color: #ff4b5c; border: 1px solid rgba(255, 75, 92, 0.2); }
        .stock-ok { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }

        .btn-view {
            width: 50px; height: 50px;
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(197, 161, 161, 0.05);
            color: var(--rose-gold);
            border: 1px solid var(--glass-border);
            transition: 0.3s;
        }
        .btn-view:hover { background: var(--rose-gold); color: #000; transform: rotate(15deg); }

        .back-circle {
            width: 44px; height: 44px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            transition: 0.3s;
        }
        .back-circle:hover { border-color: var(--rose-gold); color: var(--rose-gold); background: rgba(197, 161, 161, 0.05); }
        
        .mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body>

    <div class="search-header mb-5 shadow-lg">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <a href="<?php echo $is_admin ? '../admin/dashboard.php' : '../customer/dashboard.php'; ?>" class="text-decoration-none d-flex align-items-center">
                        <div class="back-circle me-3">
                            <i class="bi bi-arrow-left"></i>
                        </div>
                        <span class="fw-800 text-white-50 small text-uppercase" style="letter-spacing: 1px;">Terminal</span>
                    </a>
                </div>
                <div class="col-md-6">
                    <form action="search.php" method="GET">
                        <div class="search-input-group">
                            <i class="bi bi-qr-code-scan ms-2 text-white-50"></i>
                            <input type="text" name="q" class="form-control" placeholder="Search by SKU name or category..." value="<?php echo htmlspecialchars($query); ?>" autocomplete="off">
                            <button type="submit" class="btn btn-sm px-4 rounded-pill fw-800" style="background: var(--rose-gold); color: #000; font-size: 0.75rem;">EXECUTE</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-3 text-end d-none d-md-block">
                    <span class="text-white-50 small fw-bold mono uppercase">MATCHES: <span style="color: var(--rose-gold);"><?php echo sprintf("%02d", $count); ?></span></span>
                </div>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="mb-5 animate__animated animate__fadeIn">
            <h6 class="text-white-50 text-uppercase fw-800 mb-1" style="letter-spacing: 4px; font-size: 0.7rem; color: var(--rose-gold) !important;">Inventory Stream</h6>
            <h2 class="fw-800" style="letter-spacing: -1.5px;">Query: "<?php echo htmlspecialchars($query); ?>"</h2>
        </div>

        <div class="row g-4">
            <?php if($count > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    $is_low = ($row['quantity'] < 10);
                ?>
                    <div class="col-lg-4 col-md-6 animate__animated animate__fadeInUp">
                        <div class="card result-card p-4">
                            <span class="category-tag"><?php echo htmlspecialchars($row['category']); ?></span>
                            <h4 class="fw-800 mb-4" style="letter-spacing: -0.5px;"><?php echo htmlspecialchars($row['m_name']); ?></h4>
                            
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="price-text mb-3">₹<?php echo number_format($row['price'], 2); ?></div>
                                    <div class="stock-pill <?php echo $is_low ? 'stock-low' : 'stock-ok'; ?>">
                                        <i class="bi <?php echo $is_low ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'; ?> me-2"></i>
                                        <?php echo $row['quantity']; ?> UNITS
                                    </div>
                                </div>
                                
                                <a href="medicine_details.php?id=<?php echo $row['id']; ?>" class="btn-view" title="Inspect Record">
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 animate__animated animate__pulse">
                    <div class="d-inline-block p-5 rounded-circle mb-4" style="background: rgba(255,255,255,0.01); border: 1px dashed var(--glass-border);">
                        <i class="bi bi-database-exclamation display-1 text-white-50 opacity-25"></i>
                    </div>
                    <h3 class="fw-800">No Inventory Match Found</h3>
                    <p class="text-white-50 mono" style="font-size: 0.85rem;">Zero records returned for: <?php echo htmlspecialchars($query); ?></p>
                    <a href="search.php?q=" class="btn rounded-pill px-5 mt-3 fw-800 btn-sm" style="border: 1px solid var(--rose-gold); color: var(--rose-gold); letter-spacing: 1px;">RESET SEARCH</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>