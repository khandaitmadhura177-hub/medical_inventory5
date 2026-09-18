<?php
session_start();
include('../config/db_connect.php');

// Admin Security Gate
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?msg=Access Denied: Pharmacist privileges required.");
    exit();
}

// --- INTERNAL INSERT LOGIC ---
if (isset($_POST['submit_medicine'])) {
    $m_name = mysqli_real_escape_string($conn, $_POST['m_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    $price = (float)$_POST['price'];
    
    $image_name = $_FILES['medicine_image']['name'];
    if (!empty($image_name)) {
        // Create directory if it doesn't exist
        $dir = "../assets/image/medicine/";
        if (!is_dir($dir)) { mkdir($dir, 0777, true); }

        $image = time() . '_' . basename($image_name);
        $target = $dir . $image;
    } else {
        $image = "default_med.png"; 
    }
    
    $query = "INSERT INTO medicines (m_name, category, quantity, expiry_date, price, image) 
              VALUES ('$m_name', '$category', '$quantity', '$expiry', '$price', '$image')";
    
    if (mysqli_query($conn, $query)) {
        if (!empty($image_name)) {
            move_uploaded_file($_FILES['medicine_image']['tmp_name'], $target);
        }
        header("Location: manage_inventory.php?status=success&msg=Node_Registered");
        exit();
    } else {
        $error_msg = mysqli_error($conn);
        header("Location: add_medicine.php?status=error&msg=" . urlencode($error_msg));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Register Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800&display=swap');
        
        :root { 
            --bg: #030712; 
            --primary-neon: #2DD4BF; /* Neural Cyan */
            --secondary-neon: #0ea5e9; 
            --card-bg: #111827; 
            --input-bg: rgba(255, 255, 255, 0.02); 
            --border: rgba(45, 212, 191, 0.1);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            background-image: radial-gradient(circle at 0% 0%, rgba(45, 212, 191, 0.07) 0%, transparent 50%);
            color: white; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            padding: 40px 0;
        }

        .form-card { 
            background: var(--card-bg); 
            border-radius: 35px; 
            border: 1px solid var(--border); 
            box-shadow: 0 40px 100px rgba(0,0,0,0.6); 
            overflow: hidden; 
            backdrop-filter: blur(20px);
        }

        .side-accent { 
            background: linear-gradient(180deg, var(--primary-neon) 0%, var(--secondary-neon) 100%); 
            color: #000; 
            padding: 50px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .brand-icon { 
            width: 60px; height: 60px; 
            background: rgba(0, 0, 0, 0.1); 
            border-radius: 18px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.8rem; 
            margin-bottom: 25px; 
            border: 1px solid rgba(0, 0, 0, 0.05); 
        }

        .form-label { 
            font-weight: 800; 
            color: var(--primary-neon); 
            font-size: 0.65rem; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .form-control, .form-select { 
            background: var(--input-bg); 
            border: 1px solid var(--border); 
            border-radius: 14px; 
            padding: 12px 18px; 
            color: white; 
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus, .form-select:focus { 
            background: rgba(45, 212, 191, 0.05); 
            border-color: var(--primary-neon); 
            box-shadow: 0 0 15px rgba(45, 212, 191, 0.15); 
            color: white; 
        }

        .file-upload-wrapper { 
            border: 2px dashed var(--border); 
            border-radius: 20px; 
            padding: 25px; 
            text-align: center; 
            transition: 0.3s; 
        }

        .file-upload-wrapper:hover { 
            border-color: var(--primary-neon); 
            background: rgba(45, 212, 191, 0.02); 
        }

        .btn-submit { 
            background: var(--primary-neon); 
            border: none; 
            border-radius: 16px; 
            padding: 16px; 
            font-weight: 800; 
            color: #030712; 
            letter-spacing: 0.5px;
            transition: 0.4s;
        }

        .btn-submit:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 12px 25px rgba(45, 212, 191, 0.2); 
            filter: brightness(1.05);
        }

        .back-link {
            color: rgba(255,255,255,0.3);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .back-link:hover { color: var(--primary-neon); }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="form-card animate__animated animate__fadeInUp">
                <div class="row g-0">
                    <div class="col-lg-4 side-accent d-none d-lg-flex">
                        <div class="brand-icon"><i class="bi bi-box-seam"></i></div>
                        <h2 class="fw-800 mb-3" style="font-family: 'Outfit'; letter-spacing: -1px;">Inventory<br>Registry</h2>
                        <p class="small mb-5" style="color: rgba(0,0,0,0.6); font-weight: 600; line-height: 1.6;">Registering a new pharmaceutical node into the MIMS global database. All entries are tracked via pharmacist ID.</p>
                        
                        <div class="mt-auto">
                            <div class="p-3 rounded-4" style="background: rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05);">
                                <small class="d-block fw-bold mb-1"><i class="bi bi-cpu-fill me-2"></i>Automated Indexing</small>
                                <span class="small opacity-75">Product will be instantly available for customer requisition.</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <div>
                                <h3 class="fw-800 mb-1" style="letter-spacing: -1px;">New Medicine Entry</h3>
                                <span class="text-white-50 small">Synchronize medication profiles</span>
                            </div>
                            <a href="manage_inventory.php" class="back-link"><i class="bi bi-chevron-left"></i> Exit Terminal</a>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data" class="row g-4">
                            <div class="col-md-7">
                                <label class="form-label">Full Nomenclature</label>
                                <input type="text" name="m_name" class="form-control" placeholder="Product Name & Strength" required>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Classification</label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled selected>System Category</option>
                                    <?php
                                    $cat_query = "SELECT * FROM categories ORDER BY category_name ASC";
                                    $cat_result = mysqli_query($conn, $cat_query);
                                    while($cat_row = mysqli_fetch_assoc($cat_result)):
                                    ?>
                                        <option value="<?php echo htmlspecialchars($cat_row['category_name']); ?>" style="color: black;">
                                            <?php echo htmlspecialchars($cat_row['category_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Stock Quota</label>
                                <input type="number" name="quantity" class="form-control" placeholder="00" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Expiry Threshold</label>
                                <input type="date" name="expiry_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit Valuation (₹)</label>
                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Visual Asset Upload</label>
                                <div class="file-upload-wrapper">
                                    <i class="bi bi-upload text-primary-neon fs-3 mb-2 d-block"></i>
                                    <input type="file" name="medicine_image" class="form-control border-0 bg-transparent text-white-50" accept="image/*" required>
                                    <small class="text-white-50">PNG, JPG or WEBP (Max 2MB)</small>
                                </div>
                            </div>

                            <div class="col-12 mt-5">
                                <button type="submit" name="submit_medicine" class="btn btn-submit w-100">
                                    <i class="bi bi-plus-circle-fill me-2"></i> INITIALIZE SYSTEM REGISTRATION
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>