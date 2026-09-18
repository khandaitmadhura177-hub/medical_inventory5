<?php
session_start();
include('config/db_connect.php');

// 1. Fetch categories
$cat_query = "SELECT DISTINCT category FROM medicines WHERE quantity > 0";
$cat_result = mysqli_query($conn, $cat_query);

// 2. Search & Category Filter
$search = isset($_GET['q']) ? mysqli_real_escape_string($conn, $_GET['q']) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$query = "SELECT * FROM medicines WHERE quantity > 0";
if ($search) { $query .= " AND (m_name LIKE '%$search%' OR description LIKE '%$search%')"; }
if ($category_filter) { $query .= " AND category = '$category_filter'"; }
$query .= " ORDER BY id DESC LIMIT 12";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Neural Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        
        :root { 
            --neural-bg: #050505; 
            --terminal-cyan: #00f2ff; 
            --card-dark: #111111; 
            --border-glow: rgba(0, 242, 255, 0.15); 
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--neural-bg); color: #ffffff; }

        /* Updated Navbar for Logo and Store Name */
        .navbar { background: rgba(5, 5, 5, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border-glow); }
        .nav-container { display: flex; align-items: center; justify-content: space-between; width: 100%; }
        
        .logo-box { display: flex; align-items: center; gap: 10px; text-decoration: none; flex: 1; }
        .brand-logo { height: 40px; width: auto; filter: drop-shadow(0 0 5px var(--terminal-cyan)); }
        
        .store-name-center { 
            flex: 2; 
            text-align: center; 
            font-weight: 800; 
            letter-spacing: 2px; 
            color: #ffffff; 
            text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
            text-shadow: 0 0 10px rgba(0, 242, 255, 0.5);
        }
        
        .auth-box { flex: 1; text-align: right; }

        /* FIXED SEARCH BAR */
        .search-wrapper { background: #ffffff; border-radius: 50px; padding: 5px; display: flex; align-items: center; }
        .search-input { 
            background: transparent !important;
            border: none !important;
            color: #000000 !important; 
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            box-shadow: none !important;
        }
        .search-input::placeholder { color: #888888; }

        /* Filter Pills */
        .filter-container { overflow-x: auto; white-space: nowrap; padding-bottom: 15px; scrollbar-width: none; }
        .cat-pill {
            display: inline-block; padding: 8px 22px; border-radius: 50px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border-glow);
            color: #888; text-decoration: none; margin-right: 12px; transition: 0.3s;
            font-size: 0.85rem; font-family: 'JetBrains Mono', monospace;
        }
        .cat-pill:hover, .cat-pill.active { 
            background: var(--terminal-cyan); color: #000; 
            border-color: var(--terminal-cyan); box-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
        }

        /* Medicine Card */
        .medicine-card { 
            border: 1px solid var(--border-glow); border-radius: 12px; 
            background: var(--card-dark); transition: 0.4s; height: 100%; 
            display: flex; flex-direction: column; overflow: hidden;
        }
        .medicine-card:hover { border-color: var(--terminal-cyan); transform: translateY(-5px); }
        
        .img-container { 
            height: 200px; 
            background: #ffffff; 
            display: flex; align-items: center; justify-content: center; 
            margin: 10px; border-radius: 8px; overflow: hidden;
            position: relative;
        }
        .medicine-img { max-width: 90%; max-height: 90%; object-fit: contain; display: block; }

        .price-text { color: var(--terminal-cyan); font-family: 'JetBrains Mono', monospace; font-weight: 700; }
        .btn-action { background: var(--terminal-cyan); color: #000; border: none; font-weight: 700; transition: 0.3s; }
        .btn-action:hover { background: #fff; }

        .info-footer { background: #000; border-top: 1px solid var(--border-glow); margin-top: 80px; padding: 60px 0; }
        .terminal-input { background: #0a0a0a; border: 1px solid var(--border-glow); color: var(--terminal-cyan); font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg py-3 sticky-top">
    <div class="container">
        <div class="nav-container">
            <a class="logo-box" href="index.php">
                <img src="assets/image/medicine/logo.png" alt="Logo" class="brand-logo" onerror="this.src='https://cdn-icons-png.flaticon.com/512/4320/4320337.png'">
            </a>

            <div class="store-name-center d-none d-md-block">
                PHARMA_GENESIS_MEDICALS
            </div>

            <div class="auth-box">
                <?php if(isset($_SESSION['user_role'])): ?>
                    <a href="auth/logout.php" class="btn btn-sm btn-danger px-3">DISCONNECT</a>
                <?php else: ?>
                    <a href="auth/login.php" class="btn btn-sm btn-outline-info px-4">LOGIN</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<header class="py-5 text-center">
    <div class="container">
        <h1 class="display-5 fw-bold mb-4 font-monospace" style="color: var(--terminal-cyan);">MIMS</h1>
        
        <div class="col-lg-6 mx-auto mb-4">
            <div class="search-wrapper border border-info border-opacity-25">
                <form action="index.php" method="GET" class="w-100 d-flex">
                    <input type="text" name="q" class="form-control search-input px-4 shadow-none" placeholder="Enter Unit ID or Name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-action rounded-circle ms-2" style="width: 45px; height: 45px;"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <div class="filter-container">
            <a href="index.php" class="cat-pill <?php echo empty($category_filter) ? 'active' : ''; ?>">ALL_DEPARTMENTS</a>
            <?php while($cat = mysqli_fetch_assoc($cat_result)): ?>
                <a href="index.php?category=<?php echo urlencode($cat['category']); ?>" 
                   class="cat-pill <?php echo ($category_filter == $cat['category']) ? 'active' : ''; ?>">
                    <?php echo strtoupper(htmlspecialchars($cat['category'])); ?>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</header>

<div class="container">
    <div class="row g-4">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($med = mysqli_fetch_assoc($result)): ?>
            <div class="col-lg-3 col-md-6">
                <div class="medicine-card p-2">
                    <div class="img-container">
                        <?php 
                            $imgFile = $med['image'];
                            if (!empty($imgFile) && !strpos($imgFile, '.')) {
                                $imgFile .= '.jpeg';
                            }
                            $imgPath = "assets/image/medicine/" . $imgFile;
                        ?>
                        <img src="<?php echo $imgPath; ?>" 
                             class="medicine-img" 
                             alt="Unit_<?php echo $med['id']; ?>" 
                             onerror="this.src='https://cdn-icons-png.flaticon.com/512/883/883360.png'; this.parentElement.style.background='#111'; this.style.opacity='0.2';">
                    </div>
                    <div class="p-3 flex-grow-1">
                        <div class="small opacity-50 mb-1 font-monospace">[<?php echo $med['category']; ?>]</div>
                        <h6 class="fw-bold mb-3 text-white"><?php echo strtoupper(htmlspecialchars($med['m_name'])); ?></h6>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <div class="price-text">₹<?php echo number_format($med['price'], 2); ?></div>
                            <a href="auth/login.php" class="btn btn-sm btn-action"><i class="bi bi-plus-lg"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 opacity-50 font-monospace">> NO_DATA_FOUND_IN_SECTOR</div>
        <?php endif; ?>
    </div>
</div>

<footer class="info-footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-4 font-monospace" style="color: var(--terminal-cyan);">MIMS_CORE_v5.0</h5>
                <p class="text-white-50 small lh-lg">Neural Terminal protocol for medical inventory management. Secure, high-contrast, optimized data tracking.</p>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-bold mb-4 text-white">> UPLINK_FEEDBACK</h6>
                <form action="contact_process.php" method="POST">
                    <input type="email" name="email" class="form-control terminal-input mb-2" placeholder="USER_EMAIL_ID" required>
                    <textarea name="message" class="form-control terminal-input mb-3" rows="3" placeholder="MESSAGE_DATA..." required></textarea>
                    <button type="submit" class="btn btn-sm w-100 fw-bold btn-action py-2">TRANSMIT_SIGNAL</button>
                </form>
            </div>
            <div class="col-lg-4 text-white-50 small">
                <h6 class="fw-bold mb-4 text-white">> SYSTEM_LOCATION</h6>
                <p><i class="bi bi-geo-alt me-2"></i> SECTOR_MUMBAI_MH</p>
                <p><i class="bi bi-shield-check me-2"></i> ENCRYPTED_CONNECTION</p>
            </div>
        </div>
        <p class="text-center text-white-50 small mt-5 font-monospace">&copy; 2026 MIMS_NEURAL | SYSTEM_SECURE</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>