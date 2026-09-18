<?php
session_start();
include('../config/db_connect.php');

// SECURITY: Ensure user is logged in
if(!isset($_SESSION['user_id'])) { 
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

// --- NEW: Handle Quantity Update (Plus/Minus) ---
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    if(isset($_SESSION['cart'][$id])) {
        if($_GET['action'] == 'plus') {
            $_SESSION['cart'][$id]['quantity'] += 1;
        } elseif($_GET['action'] == 'minus' && $_SESSION['cart'][$id]['quantity'] > 1) {
            $_SESSION['cart'][$id]['quantity'] -= 1;
        }
    }
    header("Location: cart.php");
    exit();
}

// Handle Removing Items
if(isset($_GET['remove'])) {
    $id = mysqli_real_escape_string($conn, $_GET['remove']);
    if(isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: cart.php");
    exit();
}

// Handle Checkout
if(isset($_POST['place_order'])) {
    if(!empty($_SESSION['cart'])) {
        $_SESSION['pending_payment_mode'] = $_POST['payment_method'];
        header("Location: checkout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        :root {
            --accent-cyan: #10b981; 
            --bg-body: #090a0c;
            --card-glass: #111317;
            --text-main: #ffffff;
            --text-muted: #a0a0a0;
            --border-color: rgba(255,255,255,0.08);
            --danger-neon: #ff4d4d;
        }

        [data-theme="light"] {
            --bg-body: #f3f4f6;
            --card-glass: #ffffff;
            --text-main: #111827;
            --text-muted: #6b7280;
            --border-color: rgba(0,0,0,0.08);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        /* FIX: Ensure visibility for custom classes */
        .fw-700 { font-weight: 700; color: var(--text-main); }
        .fw-800 { font-weight: 800; color: var(--text-main); }
        .price-text { color: var(--text-main) !important; font-weight: 600; }

        .navbar { background: transparent; border-bottom: 1px solid var(--border-color); backdrop-filter: blur(10px); }
        .navbar-brand { font-weight: 800; color: var(--accent-cyan); letter-spacing: -1px; }

        .cart-container { max-width: 1000px; margin-top: 50px; }

        .cart-card { 
            background: var(--card-glass);
            border: 1px solid var(--border-color);
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            padding: 40px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.3s;
            margin-bottom: 25px;
        }
        .btn-back:hover { color: var(--accent-cyan); transform: translateX(-5px); }

        .table { --bs-table-bg: transparent; color: var(--text-main); vertical-align: middle; }
        .table thead th { 
            border: none; 
            color: var(--text-muted); 
            font-weight: 700; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
            padding-bottom: 20px;
        }

        .med-thumb-container {
            width: 60px; height: 60px;
            background: rgba(255,255,255,0.03);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--border-color);
            overflow: hidden;
        }
        .med-thumb { width: 100%; height: 100%; object-fit: cover; }

        /* Qty Toggle Styles */
        .qty-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.03);
            padding: 5px 10px;
            border-radius: 14px;
            border: 1px solid var(--border-color);
            width: fit-content;
        }
        .qty-btn {
            color: var(--accent-cyan);
            text-decoration: none;
            font-size: 1.2rem;
            line-height: 1;
            transition: 0.2s;
        }
        .qty-btn:hover { transform: scale(1.2); color: #fff; }
        .qty-val { font-weight: 800; min-width: 20px; text-align: center; color: var(--text-main); }

        .theme-toggle-btn {
            background: var(--card-glass);
            color: var(--text-main);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
        }

        .btn-checkout {
            background: var(--accent-cyan);
            color: #000;
            font-weight: 800;
            border: none;
            padding: 16px 32px;
            border-radius: 18px;
            font-size: 1rem;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-checkout:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(16, 185, 129, 0.4);
            background: #12d393;
        }

        .remove-link { color: var(--text-muted); transition: 0.2s; }
        .remove-link:hover { color: var(--danger-neon); }
    </style>
</head>
<body>

<nav class="navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fs-3" href="../customer/dashboard.php">MIMS<span class="text-white">.</span></a>
        
        <div class="d-flex gap-4 align-items-center">
            <button class="theme-toggle-btn" id="themeBtn">
                <i class="bi bi-moon-stars-fill" id="themeIcon"></i>
            </button>

            <a href="cart.php" class="position-relative text-decoration-none" style="color: var(--accent-cyan);">
                <i class="bi bi-bag-check-fill fs-4"></i>
                <?php 
                $nav_count = 0;
                if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    foreach($_SESSION['cart'] as $item) $nav_count += $item['quantity'];
                }
                if($nav_count > 0): 
                ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-dark" style="font-size: 0.6rem;">
                        <?php echo $nav_count; ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</nav>

<div class="container cart-container">
    <a href="../module/inventory.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> Back to Medicines
    </a>

    <div class="cart-card animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h6 class="text-uppercase tracking-widest text-muted fw-800 mb-2" style="font-size: 0.7rem; letter-spacing: 2px;">Shopping Cart</h6>
                <h2 class="fw-800 m-0">Review Your Items</h2>
            </div>
        </div>

        <?php if(!empty($_SESSION['cart'])): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Details</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach($_SESSION['cart'] as $id => $item): 
                            $subtotal = $item['price'] * $item['quantity'];
                            $grand_total += $subtotal;
                            $img_name = !empty($item['image']) ? basename($item['image']) : 'default.png';
                            $img_path = "../assets/image/medicine/" . $img_name;
                        ?>
                        <tr>
                            <td>
                                <div class="med-thumb-container">
                                    <img src="<?php echo $img_path; ?>" class="med-thumb" onerror="this.src='https://cdn-icons-png.flaticon.com/512/883/883356.png';">
                                </div>
                            </td>
                            <td>
                                <div class="fw-700"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="text-muted small">REF: #MED-<?php echo $id; ?></div>
                            </td>
                            <td class="price-text">₹<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <div class="qty-controls">
                                    <a href="cart.php?action=minus&id=<?php echo $id; ?>" class="qty-btn"><i class="bi bi-dash-circle"></i></a>
                                    <span class="qty-val"><?php echo $item['quantity']; ?></span>
                                    <a href="cart.php?action=plus&id=<?php echo $id; ?>" class="qty-btn"><i class="bi bi-plus-circle"></i></a>
                                </div>
                            </td>
                            <td class="fw-800" style="color: var(--accent-cyan);">₹<?php echo number_format($subtotal, 2); ?></td>
                            <td class="text-end">
                                <a href="cart.php?remove=<?php echo $id; ?>" class="remove-link">
                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="row mt-5 pt-4 border-top border-secondary border-opacity-10 align-items-center">
                <div class="col-md-7 mb-4 mb-md-0">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-circle" style="background: rgba(16, 185, 129, 0.05);">
                            <i class="bi bi-shield-lock-fill text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-700">MIMS Secure Verification</div>
                            <div class="text-muted small">Secure checkout with encrypted transaction.</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end">
                    <div class="text-muted small fw-600 mb-1">Total Amount Due</div>
                    <div class="display-6 fw-800 mb-4" style="color: var(--text-main);">₹<?php echo number_format($grand_total, 2); ?></div>
                    
                    <form method="POST">
                        <input type="hidden" name="payment_method" value="COD">
                        <button type="submit" name="place_order" class="btn btn-checkout w-100">
                            Complete Order <i class="bi bi-arrow-right-short ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-bag-x opacity-25" style="font-size: 80px;"></i>
                <h4 class="fw-800 mt-4">Your basket is empty</h4>
                <a href="../module/inventory.php" class="btn btn-checkout mt-3">
                    Browse Medicines
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const themeBtn = document.getElementById('themeBtn');
    const html = document.documentElement;
    const themeIcon = document.getElementById('themeIcon');

    const savedTheme = localStorage.getItem('mims_theme') || 'dark';
    html.setAttribute('data-theme', savedTheme);
    updateIcon(savedTheme);

    themeBtn.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('mims_theme', newTheme);
        updateIcon(newTheme);
    });

    function updateIcon(theme) {
        themeIcon.className = theme === 'dark' ? 'bi bi-moon-stars-fill' : 'bi bi-sun-fill';
    }
</script>

</body>
</html>