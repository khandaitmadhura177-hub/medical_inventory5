<?php
session_start();
include('../config/db_connect.php');

// Security Check: Only Admins can purge records
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

$success = false;
$display_ref = "Unknown SKU";

if(isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // 1. Fetch metadata before purging
    $res = mysqli_query($conn, "SELECT m_name, image FROM medicines WHERE id = $id");
    if($data = mysqli_fetch_assoc($res)) {
        $display_ref = $data['m_name'];
        $image_to_delete = $data['image'];
        
        // 2. Execute Database Purge
        if(mysqli_query($conn, "DELETE FROM medicines WHERE id = $id")) {
            $success = true;

            // 3. Physical File Erasure
            $target_dir = "../assets/image/medicine/";
            $file_path = $target_dir . $image_to_delete;

            // Security: Prevent unlinking system defaults or empty paths
            if (!empty($image_to_delete) && $image_to_delete != 'default_medicine.png' && file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Purge Sequence</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { 
            background: #030712; 
            color: #2DD4BF; 
            font-family: 'Courier New', monospace; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
            overflow: hidden;
        }
        .purge-container { text-align: center; border: 1px solid rgba(45, 212, 191, 0.2); padding: 40px; border-radius: 20px; background: rgba(15, 23, 42, 0.5); }
        .glitch { font-weight: bold; font-size: 1.2rem; letter-spacing: 2px; }
        .progress-bar-container { width: 100%; background: #1e293b; height: 4px; margin-top: 20px; border-radius: 10px; overflow: hidden; }
        .progress-fill { height: 100%; background: #2DD4BF; width: 0%; transition: width 2s linear; }
        .status-text { color: #f43f5e; font-weight: bold; }
    </style>
</head>
<body>

<div class="purge-container animate__animated animate__fadeIn">
    <div class="glitch mb-3">SYSTEM_PURGE_SEQUENCE</div>
    <div class="text-white-50 small mb-2">TARGET: <span class="text-white"><?php echo htmlspecialchars($display_ref); ?></span></div>
    
    <?php if($success): ?>
        <div class="status-text animate__animated animate__pulse animate__infinite">ERASING RECORD...</div>
        <div class="progress-bar-container">
            <div class="progress-fill" id="fill"></div>
        </div>
    <?php else: ?>
        <div class="text-danger">PURGE_FAILED: INVALID_ID</div>
    <?php endif; ?>
</div>

<script>
    // Visual progress bar animation
    if(document.getElementById('fill')) {
        setTimeout(() => { document.getElementById('fill').style.width = '100%'; }, 100);
    }

    // Redirect to inventory management
    setTimeout(() => { 
        window.location.href = 'manage_inventory.php?status=deleted'; 
    }, 2200);
</script>

</body>
</html>