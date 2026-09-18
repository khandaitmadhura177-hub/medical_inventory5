<?php
session_start();
include('../config/db_connect.php'); 

/**
 * MIMS CORE: Medicine Update Logic
 * Enhanced with validation protocols and error reporting.
 */

// 1. SECURITY: Admin Authentication Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_medicine'])) {
    
    // 2. DATA ACQUISITION & SANITIZATION
    $id          = mysqli_real_escape_string($conn, $_POST['id']);
    $m_name      = mysqli_real_escape_string($conn, $_POST['m_name']);
    $category    = mysqli_real_escape_string($conn, $_POST['category']);
    $quantity    = (int)$_POST['quantity']; 
    $price       = (float)$_POST['price'];
    $expiry_date = mysqli_real_escape_string($conn, $_POST['expiry_date']);
    
    // Validation: Ensure required fields aren't empty
    if(empty($m_name) || empty($id)) {
        header("Location: ../module/edit_medicine.php?id=$id&status=error&msg=Missing+Required+Fields");
        exit();
    }

    $image_update_sql = "";

    // 3. IMAGE UPLOAD & OLD FILE CLEANUP
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/image/medicine/";
        
        // Ensure directory exists
        if (!is_dir($target_dir)) { 
            mkdir($target_dir, 0755, true); 
        }

        $file_ext  = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $allowed   = array('jpg', 'jpeg', 'png', 'webp');
        $max_size  = 2 * 1024 * 1024; // 2MB Limit

        if (in_array($file_ext, $allowed)) {
            if ($_FILES['image']['size'] <= $max_size) {
                
                // Generate unique terminal-style filename
                $new_filename = "MED_UPD_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $file_ext;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $new_filename)) {
                    
                    // --- START CLEANUP: Delete the old image file ---
                    $old_res = mysqli_query($conn, "SELECT image FROM medicines WHERE id = '$id'");
                    if($old_row = mysqli_fetch_assoc($old_res)) {
                        $old_file_path = $target_dir . $old_row['image'];
                        
                        if (!empty($old_row['image']) && $old_row['image'] != 'default_medicine.png' && file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                    // --- END CLEANUP ---

                    $image_update_sql = ", image = '$new_filename'";
                }
            } else {
                header("Location: ../module/edit_medicine.php?id=$id&status=error&msg=File+Too+Large+Max+2MB");
                exit();
            }
        } else {
            header("Location: ../module/edit_medicine.php?id=$id&status=error&msg=Invalid+File+Type");
            exit();
        }
    }

    // 4. DATABASE EXECUTION
    $query = "UPDATE medicines SET 
                m_name = '$m_name', 
                category = '$category', 
                quantity = '$quantity', 
                price = '$price', 
                expiry_date = '$expiry_date' 
                $image_update_sql 
              WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        header("Location: ../module/inventory.php?status=updated");
        exit();
    } else {
        $db_error = mysqli_error($conn);
        header("Location: ../module/edit_medicine.php?id=$id&status=error&msg=" . urlencode($db_error));
        exit();
    }
} else {
    // If the POST is empty (often due to exceeding upload_max_filesize in php.ini)
    header("Location: ../module/inventory.php?status=error&msg=Server+Upload+Limit+Exceeded");
    exit();
}