<?php
session_start();
include('../config/db_connect.php'); 

// ---------------------------------------------------------
// SECURITY: Admin Authentication Check (Role 1) - Preserved
// ---------------------------------------------------------
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

if (isset($_POST['submit_medicine'])) {
    // ---------------------------------------------------------
    // DATA SANITIZATION: Strictly Preserving your input flow
    // ---------------------------------------------------------
    $m_name      = mysqli_real_escape_string($conn, $_POST['m_name']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    // 'quantity' matches your DB column name - Preserved
    $quantity    = (int)$_POST['quantity']; 
    $price       = (float)$_POST['price'];
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    
    // ---------------------------------------------------------
    // SECURE IMAGE UPLOAD LOGIC: Preserving your unique naming system
    // ---------------------------------------------------------
    $image_filename = "default_medicine.png"; 

    if (isset($_FILES['medicine_image']) && $_FILES['medicine_image']['error'] == 0) {
        // FIXED PATH: Preserving your exact directory structure
        $target_dir = "../assets/image/medicine/";
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_name = $_FILES["medicine_image"]["name"];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed   = array('jpg', 'jpeg', 'png', 'webp');

        if (in_array($file_ext, $allowed)) {
            // Generates a unique name - Preserving your hash logic
            $new_filename = "MED_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $file_ext;
            
            // Move file to the assets/image/medicine/ folder
            if (move_uploaded_file($_FILES["medicine_image"]["tmp_name"], $target_dir . $new_filename)) {
                $image_filename = $new_filename;
            }
        }
    }

    // ---------------------------------------------------------
    // DATABASE EXECUTION: Preserving your exact INSERT structure
    // ---------------------------------------------------------
    $query = "INSERT INTO medicines (m_name, category, quantity, price, expiry_date, image) 
              VALUES ('$m_name', '$category', '$quantity', '$price', '$expiry_date', '$image_filename')";

    // THEMED TRANSMISSION UI (Visible only during processing lag)
    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <style>
            body { background: #0a0a0a; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif; color: #ffb347; }
            .loader { text-align: center; border: 1px solid rgba(255, 179, 71, 0.2); padding: 40px; border-radius: 20px; background: rgba(20,20,20,0.8); backdrop-filter: blur(10px); }
            .spinner { border: 2px solid rgba(255, 179, 71, 0.1); border-left-color: #ffb347; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; margin-bottom: 15px; }
            @keyframes spin { to { transform: rotate(360deg); } }
            .label { font-size: 0.7rem; letter-spacing: 2px; text-transform: uppercase; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='loader'>
            <div class='spinner'></div>
            <div class='label'>Uploading to Core...</div>
        </div>
    </body>
    </html>";

    if (mysqli_query($conn, $query)) {
        header("Location: ../module/inventory.php?status=inserted"); 
        exit();
    } else {
        $db_error = mysqli_error($conn);
        header("Location: ../module/add_medicine.php?status=error&msg=" . urlencode($db_error));
        exit();
    }
} else {
    header("Location: ../module/add_medicine.php");
    exit();
}
?>