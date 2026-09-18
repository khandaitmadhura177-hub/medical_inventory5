<?php
session_start();
// Security check: If no order details are in session, bounce back to inventory
if(!isset($_SESSION['temp_order_details'])) { 
    header("Location: ../module/inventory.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Finalizing Order...</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        
        body { 
            background: #090a0c; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 100vh; 
            color: white; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0;
            overflow: hidden; 
        }

        .success-wrapper { text-align: center; max-width: 400px; padding: 20px; }

        .icon-circle {
            width: 100px;
            height: 100px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: #10b981;
            font-size: 50px;
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
        }

        h2 { font-weight: 800; letter-spacing: -0.5px; margin-bottom: 10px; }
        
        .status-text { color: #a0a0a0; font-size: 0.9rem; margin-bottom: 30px; }

        .loader-bar { 
            width: 100%; 
            height: 6px; 
            background: rgba(255,255,255,0.05); 
            border-radius: 20px; 
            position: relative; 
            overflow: hidden; 
        }

        .loader-fill { 
            position: absolute; 
            width: 0%; 
            height: 100%; 
            background: linear-gradient(90deg, #10b981, #34d399); 
            border-radius: 20px;
            animation: fillBar 2.5s cubic-bezier(0.65, 0, 0.35, 1) forwards; 
        }

        .security-badge {
            margin-top: 40px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #10b981;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            opacity: 0.8;
        }

        @keyframes fillBar { to { width: 100%; } }
    </style>
</head>
<body>

    <div class="success-wrapper animate__animated animate__fadeIn">
        <div class="icon-circle animate__animated animate__bounceIn">
            <i class="bi bi-shield-check"></i>
        </div>
        
        <h2>Finalizing Order</h2>
        <p class="status-text">Synchronizing with MIMS Medical Terminal...</p>
        
        <div class="loader-bar">
            <div class="loader-fill"></div>
        </div>

        <div class="security-badge">
            <i class="bi bi-lock-fill"></i> End-to-End Encrypted
        </div>
    </div>

    <form id="finalForm" action="confirm_order.php" method="POST" style="display:none;">
        <input type="hidden" name="execute_order" value="1">
    </form>

    <script>
        // Trigger form submission after visual bar finishes
        setTimeout(() => {
            document.getElementById('finalForm').submit();
        }, 2800);
    </script>
</body>
</html>