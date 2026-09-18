<?php
session_start();
include('../config/db_connect.php'); 

// Security Gate
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

$show_invoice = false;
$invoice_data = [];

if(isset($_POST['generate_bill'])) {
    $med_id = (int)$_POST['med_id'];
    $qty_change = (int)$_POST['qty'];
    $party_name = mysqli_real_escape_string($conn, $_POST['customer']);
    $trans_type = $_POST['trans_type']; 
    
    $invoice_no = "INV-" . strtoupper(substr(md5(time()), 0, 8));

    $res = mysqli_query($conn, "SELECT * FROM medicines WHERE id=$med_id");
    $item = mysqli_fetch_assoc($res);
    
    if($item) {
        $today = date('Y-m-d');
        
        // Safety Check: Expiry
        if($trans_type == 'sale' && !empty($item['expiry_date']) && $item['expiry_date'] < $today) {
            echo "<script>alert('CRITICAL FAILURE: CANNOT DISPENSE EXPIRED MATERIAL!'); window.location='billing.php';</script>";
            exit();
        }

        if($trans_type == 'sale') {
            if($item['quantity'] >= $qty_change) {
                mysqli_query($conn, "UPDATE medicines SET quantity = quantity - $qty_change WHERE id=$med_id");
                $total = (float)$item['price'] * $qty_change;
                
                $log_query = "INSERT INTO sales (medicine_id, patient_name, quantity_sold, sale_date) 
                              VALUES ($med_id, '$party_name', $qty_change, NOW())";
                mysqli_query($conn, $log_query);
                
                $invoice_data = [
                    'no' => $invoice_no,
                    'customer' => $party_name,
                    'item' => $item['m_name'],
                    'qty' => $qty_change,
                    'price' => $item['price'],
                    'total' => $total,
                    'date' => date('d M Y, H:i')
                ];
                $show_invoice = true;
            } else {
                echo "<script>alert('ERROR: INSUFFICIENT NODE CAPACITY!'); window.location='billing.php';</script>";
                exit();
            }
        } else {
            mysqli_query($conn, "UPDATE medicines SET quantity = quantity + $qty_change WHERE id=$med_id");
            echo "<script>alert('INVENTORY RESTOCK SEQUENCE COMPLETE'); window.location='billing.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Terminal POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #030712;
            --neural-cyan: #2DD4BF;
            --card-bg: #0f172a;
            --input-bg: #1e293b;
            --border: rgba(45, 212, 191, 0.1);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            color: white;
            min-height: 100vh;
            background-image: radial-gradient(circle at 0% 0%, rgba(45, 212, 191, 0.03) 0%, transparent 50%);
        }

        .billing-panel {
            background: var(--card-bg);
            border-radius: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }

        .form-label { color: var(--neural-cyan); font-size: 0.7rem; text-transform: uppercase; font-weight: 800; letter-spacing: 1.5px; }
        
        .form-control, .form-select {
            background: var(--input-bg);
            border: 1px solid var(--border);
            color: white;
            border-radius: 15px;
            padding: 14px;
        }

        .form-control:focus, .form-select:focus {
            background: var(--input-bg);
            color: white;
            border-color: var(--neural-cyan);
            box-shadow: 0 0 20px rgba(45, 212, 191, 0.1);
        }

        .thermal-receipt {
            background: #ffffff;
            padding: 40px;
            border-radius: 2px;
            font-family: 'JetBrains Mono', monospace;
            color: #111;
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            position: relative;
        }

        .thermal-receipt::before {
            content: "";
            position: absolute;
            top: -10px; left: 0; width: 100%; height: 10px;
            background: linear-gradient(-45deg, transparent 5px, white 5px), linear-gradient(45deg, transparent 5px, white 5px);
            background-size: 10px 10px;
        }

        .btn-pos {
            background: var(--neural-cyan);
            border: none;
            border-radius: 18px;
            padding: 18px;
            font-weight: 800;
            color: #030712;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-pos:hover { 
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(45, 212, 191, 0.3);
            background: #26bba8;
        }

        @media print { 
            .no-print { display: none !important; } 
            body { background: white; padding: 0; }
            .container { width: 100%; max-width: 100%; }
            .thermal-receipt { box-shadow: none; width: 100%; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row g-5 justify-content-center">
        <div class="col-lg-6 no-print">
            <div class="billing-panel p-5 animate__animated animate__fadeInLeft">
                <div class="mb-5">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width: 10px; height: 10px; background: var(--neural-cyan); border-radius: 50%;" class="animate__animated animate__pulse animate__infinite"></div>
                        <span class="text-white-50 small fw-bold text-uppercase tracking-widest">System Online: POS-TERMINAL</span>
                    </div>
                    <h2 class="fw-800">Transaction <span style="color:var(--neural-cyan)">Portal</span></h2>
                </div>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label">Client Identity</label>
                        <input type="text" name="customer" class="form-control" placeholder="Enter patient name..." required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="form-label">Protocol</label>
                            <select name="trans_type" class="form-select">
                                <option value="sale">Outbound Sale</option>
                                <option value="restock">Inbound Restock</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Unit Count</label>
                            <input type="number" name="qty" id="qtyInput" class="form-control" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Material Selection</label>
                        <select name="med_id" id="medSelect" class="form-select" required>
                            <option value="">Select Medication</option>
                            <?php
                            $stock = mysqli_query($conn, "SELECT id, m_name, quantity, price, expiry_date FROM medicines ORDER BY m_name ASC");
                            while($s = mysqli_fetch_assoc($stock)) {
                                $is_exp = (!empty($s['expiry_date']) && $s['expiry_date'] < date('Y-m-d')) ? " [CRITICAL: EXPIRED]" : "";
                                echo "<option value='{$s['id']}' data-price='{$s['price']}'>{$s['m_name']} (Stk: {$s['quantity']}) $is_exp</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" name="generate_bill" class="btn btn-pos w-100 mb-3">
                        Execute Transaction
                    </button>
                    <a href="../admin/dashboard.php" class="btn btn-link text-white-50 w-100 text-decoration-none small">Abort & Return</a>
                </form>
            </div>
        </div>

        <div class="col-lg-5">
            <?php if($show_invoice): ?>
            <div class="thermal-receipt animate__animated animate__zoomIn">
                <div class="text-center mb-4 pb-3" style="border-bottom: 2px dashed #000;">
                    <h4 class="fw-800 mb-0">MIMS PHARMACY</h4>
                    <small class="fw-bold">TERMINAL RECPT #<?php echo substr($invoice_data['no'], 4); ?></small>
                </div>

                <div class="small mb-4">
                    DATE: <?php echo strtoupper($invoice_data['date']); ?><br>
                    ID: <?php echo $invoice_data['no']; ?><br>
                    CLIENT: <?php echo strtoupper(htmlspecialchars($invoice_data['customer'])); ?>
                </div>

                <table class="table table-sm table-borderless mb-4">
                    <thead>
                        <tr style="border-bottom: 1px solid #000;">
                            <th class="ps-0">DESCRIPTION</th>
                            <th class="text-center">QTY</th>
                            <th class="text-end pe-0">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-0 pt-2 fw-bold"><?php echo htmlspecialchars($invoice_data['item']); ?></td>
                            <td class="text-center pt-2"><?php echo $invoice_data['qty']; ?></td>
                            <td class="text-end pt-2 pe-0">₹<?php echo number_format($invoice_data['total'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="pt-3 text-end" style="border-top: 2px solid #000;">
                    <div class="h3 fw-800 mb-0">TOTAL: ₹<?php echo number_format($invoice_data['total'], 2); ?></div>
                </div>

                <div class="text-center mt-5">
                    <p class="small mb-0">SYSTEM AUTHENTICATED TRANSACTION</p>
                    <div class="mt-2" style="font-size: 0.6rem;">Visit again for your healthcare needs.</div>
                </div>

                <div class="mt-4 pt-3 border-top no-print text-center">
                    <button onclick="window.print()" class="btn btn-dark w-100 rounded-pill mb-2">
                        <i class="bi bi-printer me-2"></i>PRINT RECEIPT
                    </button>
                    <a href="billing.php" class="btn btn-outline-dark w-100 rounded-pill">NEXT ENTRY</a>
                </div>
            </div>
            <?php else: ?>
            <div class="no-print d-none d-lg-flex h-100 align-items-center justify-content-center">
                <div class="text-center" style="opacity: 0.15">
                    <i class="bi bi-shield-lock display-1"></i>
                    <p class="mt-3 fw-bold tracking-widest">SECURE PAYMENT CHANNEL</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>