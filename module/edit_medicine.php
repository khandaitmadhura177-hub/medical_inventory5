<?php
session_start();
include('../config/db_connect.php');

// Security Check: Only Level 1 Admins
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=access_denied");
    exit();
}

// --- UPDATE LOGIC ---
if (isset($_POST['update_medicine'])) {
    $id = (int)$_POST['id'];
    $m_name = mysqli_real_escape_string($conn, $_POST['m_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $expiry = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    $price = (float)$_POST['price'];
    
    $img_query = "";
    if (!empty($_FILES['image']['name'])) {
        // Fetch old image to delete it later
        $old_img_res = mysqli_query($conn, "SELECT image FROM medicines WHERE id=$id");
        $old_img_data = mysqli_fetch_assoc($old_img_res);

        $image_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = "MED_" . time() . '.' . $image_ext;
        $target = "../assets/image/medicine/" . $image;
        
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $img_query = ", image='$image'";
            
            // Physical erasure of old asset if it's not the default
            if(!empty($old_img_data['image']) && $old_img_data['image'] != 'default_medicine.png') {
                $old_path = "../assets/image/medicine/" . $old_img_data['image'];
                if(file_exists($old_path)) {
                    @unlink($old_path);
                }
            }
        }
    }
    
    $update_sql = "UPDATE medicines SET 
                    m_name='$m_name', 
                    category='$category', 
                    quantity='$quantity', 
                    expiry_date='$expiry', 
                    price='$price' 
                    $img_query 
                   WHERE id=$id";
                   
    if (mysqli_query($conn, $update_sql)) {
        header("Location: manage_inventory.php?status=updated&msg=Node_Data_Synchronized");
        exit();
    }
}

// Fetch existing data for populate
if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = mysqli_query($conn, "SELECT * FROM medicines WHERE id = $id");
    $med = mysqli_fetch_assoc($res);
    if(!$med) { header("Location: manage_inventory.php"); exit(); }
} else { 
    header("Location: manage_inventory.php"); 
    exit(); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Edit Specifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&family=JetBrains+Mono&display=swap');
        
        :root { 
            --bg: #060709; 
            --primary-neon: #00d2ff; 
            --card-bg: #0f1115; 
            --input-bg: rgba(255, 255, 255, 0.03); 
            --border: rgba(0, 210, 255, 0.15);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: var(--bg); 
            background-image: radial-gradient(circle at 20% 30%, rgba(0, 210, 255, 0.07) 0%, transparent 50%);
            color: #e2e8f0; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
        }

        .form-card { 
            background: var(--card-bg); 
            border-radius: 40px; 
            border: 1px solid var(--border); 
            box-shadow: 0 40px 100px rgba(0,0,0,0.8); 
            overflow: hidden; 
        }

        .side-accent { 
            background: linear-gradient(180deg, #00d2ff 0%, #3a7bd5 100%); 
            padding: 50px; 
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #000;
        }

        .form-label { 
            font-weight: 800; 
            color: var(--primary-neon); 
            font-size: 0.65rem; 
            text-transform: uppercase; 
            letter-spacing: 2.5px; 
        }

        .form-control, .form-select { 
            border: 1px solid var(--border); 
            border-radius: 15px; 
            padding: 14px 20px; 
            background: var(--input-bg); 
            color: white; 
            font-size: 0.95rem;
            transition: 0.3s;
        }

        .form-control:focus, .form-select:focus { 
            background: rgba(255,255,255,0.08); 
            border-color: var(--primary-neon); 
            box-shadow: 0 0 25px rgba(0, 210, 255, 0.15);
            color: white;
        }

        .current-preview { 
            width: 180px; height: 180px; 
            object-fit: cover; 
            border-radius: 35px; 
            border: 5px solid rgba(255,255,255,0.2); 
            box-shadow: 0 20px 50px rgba(0,0,0,0.4); 
            transition: 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-update { 
            background: var(--primary-neon); 
            border: none; 
            border-radius: 20px; 
            padding: 20px; 
            font-weight: 800; 
            color: #000; 
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: 0.4s;
        }

        .btn-update:hover { 
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 210, 255, 0.4); 
        }

        .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; }
        
        option { background-color: #1a1d21; color: white; }
    </style>
</head>
<body>

<div class="container py-5 animate__animated animate__zoomIn">
    <div class="row justify-content-center">
        <div class="col-xl-11">
            <div class="form-card">
                <div class="row g-0">
                    <div class="col-lg-4 side-accent">
                        <?php 
                            $filename = $med['image'] ? $med['image'] : 'default_medicine.png'; 
                            $img_path = "../assets/image/medicine/" . $filename;
                        ?>
                        <img src="<?php echo $img_path; ?>" id="previewImg" class="current-preview mb-4">
                        <div class="text-center">
                            <h4 class="fw-800 mb-0">ASSET_ID: <?php echo str_pad($med['id'], 3, '0', STR_PAD_LEFT); ?></h4>
                            <p class="mono opacity-75 mt-2">SECURE_STORAGE_NODE</p>
                        </div>
                    </div>

                    <div class="col-lg-8 p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-start mb-5">
                            <div>
                                <h2 class="fw-800 mb-1" style="letter-spacing: -1.5px;">Edit Specifications</h2>
                                <p class="text-white-50 small">Synchronizing data parameters for <span class="text-primary-neon fw-bold"><?php echo htmlspecialchars($med['m_name']); ?></span></p>
                            </div>
                            <a href="manage_inventory.php" class="btn btn-outline-light btn-sm rounded-pill px-3 border-opacity-25">
                                <i class="bi bi-arrow-left me-1"></i> Return
                            </a>
                        </div>

                        <form action="" method="POST" enctype="multipart/form-data" class="row g-4">
                            <input type="hidden" name="id" value="<?php echo $med['id']; ?>">
                            
                            <div class="col-md-12">
                                <label class="form-label">Medical Designation</label>
                                <input type="text" name="m_name" class="form-control" value="<?php echo htmlspecialchars($med['m_name']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Node Classification</label>
                                <select name="category" class="form-select" required>
                                    <?php
                                    $cat_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");
                                    while($cat = mysqli_fetch_assoc($cat_res)):
                                        $selected = ($med['category'] == $cat['category_name']) ? 'selected' : '';
                                        echo "<option value='{$cat['category_name']}' $selected>{$cat['category_name']}</option>";
                                    endwhile;
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Inventory Quantity</label>
                                <input type="number" name="quantity" class="form-control" value="<?php echo $med['quantity']; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Expiry Timestamp</label>
                                <input type="date" name="expiry_date" class="form-control" value="<?php echo $med['expiry_date']; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unit Value (INR)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0 text-white-50">₹</span>
                                    <input type="number" step="0.01" name="price" class="form-control border-start" value="<?php echo $med['price']; ?>" required>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">System Visual Update</label>
                                <input type="file" name="image" id="imgInput" class="form-control" accept="image/*">
                                <div class="mono mt-2 text-white-25 small" style="font-size: 0.6rem;">Leave null to maintain current encrypted asset.</div>
                            </div>

                            <div class="col-12 mt-5">
                                <button type="submit" name="update_medicine" class="btn btn-update w-100">
                                    <i class="bi bi-shield-check me-2"></i> Commit Specifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Live Image Preview Logic
    imgInput.onchange = evt => {
        const [file] = imgInput.files
        if (file) {
            previewImg.src = URL.createObjectURL(file);
            previewImg.classList.add('animate__animated', 'animate__pulse');
        }
    }
</script>

</body>
</html>