<?php
// Session check is handled by the calling page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        :root {
            --bg: #0f1012;
            --rose-gold: #b76e79;
            --card-bg: #16171a;
            --accent-cyan: #00e5ff; 
            --glass: rgba(255, 255, 255, 0.03);
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: white; 
        }

        /* --- NAVIGATION --- */
        .navbar {
            background: rgba(15, 16, 18, 0.9) !important;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(183, 110, 121, 0.15);
            padding: 18px 0;
        }

        .brand-logo {
            font-weight: 900;
            letter-spacing: 2px;
            color: var(--accent-cyan) !important;
            text-decoration: none;
            font-size: 1.4rem;
        }

        .brand-logo span { color: white; }

        .nav-link {
            color: rgba(255,255,255,0.7) !important;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: 0.3s;
            margin: 0 10px;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--rose-gold) !important;
        }

        /* --- CART BADGE --- */
        .cart-icon-wrapper {
            padding: 8px 15px;
            background: var(--glass);
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: 0.3s;
        }
        .cart-icon-wrapper:hover { border-color: var(--accent-cyan); }
        .badge-cyan {
            background-color: var(--accent-cyan);
            color: #000;
            font-weight: 800;
        }

        .btn-auth-action {
            padding: 8px 25px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.75rem;
            letter-spacing: 1px;
            transition: 0.3s;
            text-decoration: none;
        }

        .btn-login { color: white; border: 1px solid rgba(255,255,255,0.1); }
        .btn-login:hover { background: white; color: black; }

        .btn-join { background: var(--rose-gold); color: white; border: none; }
        .btn-join:hover { background: #a35d67; transform: translateY(-2px); }

        .btn-logout { 
            color: #ff4d4d; 
            border: 1px solid rgba(255,77,77,0.2);
            background: rgba(255,77,77,0.05);
        }
        .btn-logout:hover { background: #ff4d4d; color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="brand-logo" href="../index.php">
            MIMS<span>STORE</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list text-white fs-2"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                
                <?php if(isset($_SESSION['role'])): ?>
                    
                    <?php if($_SESSION['role'] == 1): // ADMIN LINKS ?>
                        <li class="nav-item"><a class="nav-link" href="../admin/dashboard.php">Control Panel</a></li>
                        <li class="nav-item"><a class="nav-link" href="../admin/medicines.php">Inventory</a></li>
                        <li class="nav-item"><a class="nav-link" href="../admin/orders.php">Sales Data</a></li>
                    
                    <?php else: // CUSTOMER LINKS ?>
                        <li class="nav-item"><a class="nav-link" href="../customer/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="../customer/home.php">Shop</a></li>
                        <li class="nav-item">
                            <a class="nav-link cart-icon-wrapper position-relative" href="../customer/cart.php">
                                <i class="bi bi-bag-heart-fill fs-5" style="color: var(--accent-cyan);"></i>
                                <?php if(!empty($_SESSION['cart'])): ?>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill badge-cyan">
                                        <?php echo count($_SESSION['cart']); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-3">
                        <a class="btn-auth-action btn-logout" href="../auth/logout.php">LOGOUT</a>
                    </li>

                <?php else: // NOT LOGGED IN ?>
                    <li class="nav-item"><a class="btn-auth-action btn-login me-2" href="../auth/login.php">LOGIN</a></li>
                    <li class="nav-item"><a class="btn-auth-action btn-join" href="../auth/register.php">JOIN MIMS</a></li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>