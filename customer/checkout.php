<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }

/**
 * MIMS SECURE CHECKOUT
 * Supports both standard Cart and "Buy Now" Direct items.
 */

if (isset($_GET['id']) && isset($_GET['direct'])) {
    $med_id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM medicines WHERE id = '$med_id'");
    $med = mysqli_fetch_assoc($res);
    if ($med) {
        $total = $med['price'];
        $_SESSION['is_direct'] = true;
        $_SESSION['direct_item'] = $med;
    }
} elseif (!empty($_SESSION['cart'])) {
    $total = 0;
    foreach($_SESSION['cart'] as $item) { $total += ($item['price'] * $item['quantity']); }
    $_SESSION['is_direct'] = false;
} else {
    header("Location: ../module/inventory.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Secure Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        
        :root {
            --accent-cyan: #10b981; 
            --bg-body: #090a0c;
            --card-glass: #111317;
            --text-main: #ffffff;
            --text-muted: #a0a0a0;
            --border-color: rgba(255,255,255,0.15); /* Slightly brighter border */
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            min-height: 100vh;
        }

        .checkout-card { 
            background: var(--card-glass);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
        }

        /* FIX: Labels were too dark in your screenshot */
        .form-label-bright {
            color: #e0e0e0 !important;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: block;
        }

        /* FIX: Input field visibility and Autocomplete issues */
        .form-control, .form-select { 
            background: rgba(255,255,255,0.07) !important; 
            border: 1px solid var(--border-color); 
            color: white !important;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }

        /* This fixes the "Yellow/Blue" background when browser auto-fills */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus {
            -webkit-text-fill-color: white !important;
            -webkit-box-shadow: 0 0 0px 1000px #1a1c21 inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .form-select option {
            background: #111317; /* Dark background for dropdown options */
            color: white;
        }

        .form-control::placeholder {
            color: rgba(255,255,255,0.4) !important;
        }

        .form-control:focus, .form-select:focus { 
            border-color: var(--accent-cyan); 
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.1); 
            background: rgba(255,255,255,0.1) !important;
        }

        .price-summary-card { 
            background: rgba(16, 185, 129, 0.05); 
            border: 1px solid rgba(16, 185, 129, 0.2); 
            border-radius: 20px; 
            backdrop-filter: blur(10px);
        }

        .btn-back {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .btn-back:hover { color: var(--accent-cyan); transform: translateX(-5px); }

        .btn-confirm {
            background: var(--accent-cyan);
            color: #000;
            font-weight: 800;
            border: none;
            padding: 18px;
            border-radius: 14px;
            transition: 0.3s;
        }
        .btn-confirm:hover { 
            background: #0ea371; 
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
        }

        .btn-check:checked + .btn-outline-success {
            background-color: var(--accent-cyan) !important;
            color: #000 !important;
            border-color: var(--accent-cyan) !important;
        }
    </style>
</head>
<body onload="calculateLiveTotal()">

<div class="container py-5">
    <a href="cart.php" class="btn-back">
        <i class="bi bi-arrow-left me-2"></i> Return to Cart
    </a>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="checkout-card shadow-lg">
                <h3 class="fw-800 mb-4"><i class="bi bi-shield-lock me-2 text-success"></i>Secure Checkout</h3>
                
                <form action="confirm_order.php" method="POST" id="checkoutForm">
                    <input type="hidden" name="is_direct" value="<?php echo $_SESSION['is_direct'] ? '1' : '0'; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label-bright">Full Name</label>
                        <input type="text" name="customer_name" class="form-control rounded-pill" placeholder="Enter recipient name" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label-bright">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-pill" placeholder="email@example.com" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label-bright">Phone Number</label>
                            <input type="tel" name="phone" class="form-control rounded-pill" placeholder="+91 XXXX-XXXXXX" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-bright">Delivery Address</label>
                        <textarea name="address" class="form-control rounded-4" rows="3" placeholder="Street, City, State, Zip Code" required></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-bright">Discount Category</label>
                        <select name="discount_type" id="discount_type" class="form-select rounded-pill" onchange="calculateLiveTotal()">
                            <option value="0">Standard (No Discount)</option>
                            <option value="15">Senior Citizen (15% Off)</option>
                            <option value="10">Student / Medical Staff (10% Off)</option>
                            <option value="5">New Customer (5% Off)</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="form-label-bright mb-3">Payment Mode</label>
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" name="payment_mode" value="ONLINE" id="pay1" checked class="btn-check">
                                <label class="btn btn-outline-success w-100 py-3 rounded-4 fw-bold" for="pay1">
                                    <i class="bi bi-credit-card me-2"></i>ONLINE
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" name="payment_mode" value="COD" id="pay2" class="btn-check">
                                <label class="btn btn-outline-success w-100 py-3 rounded-4 fw-bold" for="pay2">
                                    <i class="bi bi-cash-stack me-2"></i>CASH (COD)
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-confirm w-100 text-uppercase">
                        Confirm Purchase <i class="bi bi-chevron-right ms-2"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="price-summary-card p-4 sticky-top" style="top: 20px;">
                <h5 class="fw-800 mb-4" style="color: var(--accent-cyan);">Order Summary</h5>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Subtotal</span>
                    <span class="fw-bold text-white">₹<span id="display_subtotal"><?php echo number_format($total, 2, '.', ''); ?></span></span>
                </div>
                
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Discount</span>
                    <span class="fw-bold text-success">- ₹<span id="display_discount">0.00</span></span>
                </div>

                <div class="d-flex justify-content-between mb-3 border-top border-secondary border-opacity-25 pt-3">
                    <span class="fw-600">Total Payable</span>
                    <h3 class="fw-800 mb-0" style="color: var(--accent-cyan);">₹<span id="display_total"><?php echo number_format($total, 2, '.', ''); ?></span></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calculateLiveTotal() {
    const subtotal = parseFloat(document.getElementById('display_subtotal').innerText);
    const discountPercent = parseFloat(document.getElementById('discount_type').value);
    
    const discountAmount = (subtotal * discountPercent) / 100;
    const finalTotal = subtotal - discountAmount;

    document.getElementById('display_discount').innerText = discountAmount.toFixed(2);
    document.getElementById('display_total').innerText = finalTotal.toFixed(2);
}
</script>

</body>
</html>