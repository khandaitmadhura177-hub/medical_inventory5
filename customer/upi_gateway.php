<?php
session_start();
// Match the session key used in your process_payment.php
if(!isset($_SESSION['temp_order'])) { header("Location: inventory.php"); exit(); }
$total = $_SESSION['temp_order']['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Secure UPI Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        
        :root {
            --neural-cyan: #2DD4BF;
            --neural-bg: #030712;
        }

        body { 
            background: var(--neural-bg); 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh;
            background-image: radial-gradient(circle at 50% 50%, rgba(45, 212, 191, 0.03) 0%, transparent 70%);
        }

        .payment-box { 
            background: #0f172a; 
            border: 1px solid rgba(45, 212, 191, 0.2); 
            border-radius: 35px; 
            padding: 40px; 
            text-align: center; 
            max-width: 450px; 
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .timer { 
            font-size: 1.8rem; 
            font-weight: 800; 
            color: #f43f5e; /* Pulse Red for urgency */
            margin: 15px 0; 
            font-variant-numeric: tabular-nums;
        }

        .qr-card { 
            background: white; 
            padding: 15px; 
            border-radius: 24px; 
            display: inline-block; 
            box-shadow: 0 0 20px rgba(45, 212, 191, 0.15);
        }

        .qr-card img {
            width: 220px;
            height: 220px;
        }

        .amount-display {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--neural-cyan);
            margin: 10px 0;
        }

        .btn-verify {
            background: var(--neural-cyan);
            color: #0f172a;
            font-weight: 800;
            border: none;
            padding: 16px;
            border-radius: 18px;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-verify:hover {
            background: #22d3ee;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(45, 212, 191, 0.2);
        }

        .secure-badge {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <div class="payment-box">
        <div class="timer" id="countdown">05:00</div>
        <p class="small text-white-50 mb-4">Neural Payment Link Active</p>
        
        <div class="qr-card mb-4">
            <?php 
                $upi_id = "MIMS@bank"; // Replace with your actual UPI ID
                $name = "MIMS Store";
                $qr_data = "upi://pay?pa=$upi_id&pn=" . urlencode($name) . "&am=$total&cu=INR";
            ?>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?php echo urlencode($qr_data); ?>" alt="Secure QR">
        </div>

        <div class="amount-display">₹<?php echo number_format($total, 2); ?></div>
        <p class="small opacity-50 mb-4">Scan with GPay, PhonePe, or Any UPI App</p>

        <form action="place_order_logic.php" method="POST">
            <input type="hidden" name="confirm_order" value="1">
            <input type="hidden" name="payment_mode" value="ONLINE">
            <input type="hidden" name="customer_name" value="<?php echo $_SESSION['temp_order']['name']; ?>">
            <input type="hidden" name="phone" value="<?php echo $_SESSION['temp_order']['phone']; ?>">
            <input type="hidden" name="address" value="<?php echo $_SESSION['temp_order']['address']; ?>">
            <input type="hidden" name="pincode" value="<?php echo $_SESSION['temp_order']['pincode']; ?>">
            <input type="hidden" name="claimed_category" value="<?php echo $_SESSION['temp_order']['category']; ?>">

            <button type="submit" class="btn btn-verify w-100">
                <i class="bi bi-shield-lock-fill me-2"></i> I Have Completed Payment
            </button>
        </form>

        <div class="secure-badge">
            <i class="bi bi-lock-fill text-success"></i> 256-bit Encrypted Transaction
        </div>
    </div>

    <script>
        let time = 300; 
        const display = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            let mins = Math.floor(time / 60);
            let secs = time % 60;
            display.innerHTML = `${mins < 10 ? '0' : ''}${mins}:${secs < 10 ? '0' : ''}${secs}`;
            
            if (time <= 0) {
                clearInterval(timer);
                window.location.href = "checkout.php?error=timeout";
            }
            time--;
        }, 1000);
    </script>
</body>
</html>