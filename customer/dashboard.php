<?php
session_start();
include('../config/db_connect.php');

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
if(!isset($_SESSION['user_id'])) { header("Location: ../auth/login.php"); exit(); }
$user_id = $_SESSION['user_id'];

if(isset($_POST['update_profile'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? ''); 
    $gender = mysqli_real_escape_string($conn, $_POST['gender'] ?? '');
    
    $height_type = $_POST['height_type'] ?? 'cm';
    if ($height_type == 'ft') {
        $ft = (float)($_POST['height_ft'] ?? 0);
        $in = (float)($_POST['height_in'] ?? 0);
        $height = ($ft * 30.48) + ($in * 2.54);
    } else {
        $height = (float)$_POST['height'];
    }

    $weight = (float)$_POST['weight'];
    $dob = $_POST['birthdate'];
    $blood = mysqli_real_escape_string($conn, $_POST['blood_group']);

    $update_query = "UPDATE users SET 
        first_name='$fname', last_name='$lname', phone='$phone', 
        gender='$gender', height='$height', weight='$weight', 
        birthdate='$dob', blood_group='$blood' 
        WHERE id='$user_id'";
    
    if(mysqli_query($conn, $update_query)) {
        echo "<script>alert('Health Profile Synchronized.'); window.location.href='dashboard.php';</script>";
    }
}

$user_res = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user_data = mysqli_fetch_assoc($user_res);
$display_name = $user_data['username'] ?? 'User';
$user_email = $user_data['email'] ?? 'N/A';
$u_phone = $user_data['phone'] ?? 'N/A';
$u_gender = $user_data['gender'] ?? 'N/A';

$u_height = $user_data['height'] ?? 0; 
$u_weight = $user_data['weight'] ?? 0; 
$u_dob = $user_data['birthdate'] ?? null;
$u_bg = $user_data['blood_group'] ?? 'N/A';

$age_val = "N/A";
if($u_dob && $u_dob != '0000-00-00') {
    $birthDate = new DateTime($u_dob);
    $age_val = $birthDate->diff(new DateTime('today'))->y;
}

$bmi_val = 0;
$health_status = "PENDING";
$health_msg = "Update profile for analysis.";

if($u_height > 0 && $u_weight > 0) {
    $height_in_m = $u_height / 100;
    $bmi_val = round($u_weight / ($height_in_m * $height_in_m), 1);

    if($bmi_val < 18.5) { 
        $health_status = "UNDERWEIGHT"; 
        $health_msg = "Consider increasing caloric intake with nutrient-dense foods.";
    } else if($bmi_val < 25) { 
        $health_status = "HEALTHY"; 
        $health_msg = "Great job! Maintain your current balanced diet and exercise.";
    } else if($bmi_val < 30) {
        $health_status = "OVERWEIGHT"; 
        $health_msg = "Incorporating more cardio and monitoring sugar intake is advised.";
    } else {
        $health_status = "OBESE";
        $health_msg = "Consult a specialist for a structured health plan.";
    }
}

$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, SUM(total_amount) as spent FROM orders WHERE user_id = '$user_id'"));
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$user_id' ORDER BY id DESC LIMIT 8");
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;800;900&display=swap');
        
        :root[data-theme="dark"] { --bg: #030712; --panel: #0b0e14; --text: #F8FAFC; --border: rgba(45, 212, 191, 0.2); --cyan: #2DD4BF; --row: #111827; }
        :root[data-theme="light"] { --bg: #F8FAFC; --panel: #FFFFFF; --text: #0F172A; --border: rgba(13, 148, 136, 0.3); --cyan: #0D9488; --row: #f1f5f9; }

        body { background: var(--bg); color: var(--text); font-family: 'Plus Jakarta Sans', sans-serif; transition: 0.3s; }
        .glass-card { background: var(--panel); border: 1px solid var(--border); border-radius: 25px; padding: 25px; height: 100%; }
        
        /* Revised Header Styling with reduced logo size */
        .nav-container { border-bottom: 1px solid var(--border); padding-bottom: 20px; margin-bottom: 40px; }
        /* Logo reduced to 24px height */
        /* Line 100: Change logo size here */
        /* Change '24px' to a larger value like '45px' or '50px' */
        .brand-logo { height: 45px; width: auto; filter: drop-shadow(0 0 8px rgba(45, 212, 191, 0.2)); }
       /* Line 102: Change divider size here to match */
        .v-divider { width: 1px; height: 18px; background: var(--border); margin: 0 12px; }

        .stat-box { border: 2px solid var(--cyan); border-radius: 20px; padding: 30px; text-align: center; background: rgba(45, 212, 191, 0.02); }
        .stat-label { color: var(--cyan); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; }
        .stat-value { font-family: 'Outfit'; font-size: 2.5rem; font-weight: 700; display: block; }

        .logistics-container { background: #000; border: 1px solid var(--border); border-radius: 15px; overflow: hidden; }
        .table { color: #fff !important; margin-bottom: 0; }
        .table thead th { background: #111827 !important; color: var(--cyan) !important; padding: 15px; border: none; font-size: 0.7rem; }
        .table tbody td { background: #000 !important; color: #fff !important; padding: 18px 15px; border-bottom: 1px solid #1f2937 !important; }
        
        .badge-status { background: rgba(45, 212, 191, 0.15); color: var(--cyan); border: 1px solid var(--cyan); padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 800; }
        .text-cyan { color: var(--cyan) !important; }

        .health-mini-box { border: 1px solid var(--border); border-radius: 15px; padding: 12px; text-align: center; background: var(--row); }

        .custom-modal-content { background: var(--panel); border: 1px solid var(--border); border-radius: 25px; color: var(--text); overflow: hidden; }
        .modal-sidebar { background: rgba(255,255,255,0.03); border-right: 1px solid var(--border); padding: 25px; }
        .nav-btn { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 12px; border: none; background: transparent; color: var(--text); text-align: left; font-weight: 600; }
        .nav-btn.active { background: var(--cyan); color: #000; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-control-custom { background: var(--row); border: 1px solid var(--border); color: white; border-radius: 10px; padding: 10px; width: 100%; }
        
        .height-unit-pill { font-size: 0.7rem; border-radius: 8px; cursor: pointer; transition: 0.2s; border: 1px solid var(--border); padding: 2px 8px; }
        .height-unit-pill.active { background: var(--cyan); color: #000; border-color: var(--cyan); }
    </style>
</head>
<body class="p-4">

<div class="container">
    <div class="d-flex justify-content-between align-items-center nav-container">
        <div class="d-flex align-items-center">
            <img src="../assets/image/medicine/logo.png" alt="Logo" class="brand-logo">
            <div class="v-divider"></div>
            <h3 class="fw-900 m-0" style="font-family: 'Outfit'; letter-spacing: -0.5px;">MIMS<span style="color: var(--cyan);">.HEALTH</span></h3>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-4" onclick="toggleTheme()">MODE</button>
            <button class="btn btn-outline-light rounded-pill btn-sm px-4" data-bs-toggle="modal" data-bs-target="#profileModal">MY ACCOUNT</button>
            <a href="../auth/logout.php" class="btn btn-outline-danger rounded-pill btn-sm px-4">LOGOUT</a>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md-7">
            <h1 class="display-4 fw-900 mb-2" style="font-family: 'Outfit';">Welcome, <span style="color: var(--cyan);"><?php echo $display_name; ?></span></h1>
            <p class="opacity-50 fs-6">Active Session Verified. Accessing Medical Dispatch Stream...</p>
            <div class="d-flex gap-3 mt-4">
                <a href="../module/inventory.php" class="btn px-4 py-2" style="background: var(--cyan); color: #000; font-weight: 800; border-radius: 12px; box-shadow: 0 4px 15px rgba(45, 212, 191, 0.2);">OPEN DISPENSARY</a>
                <a href="my_orders.php" class="btn btn-outline-light px-4 py-2" style="border-radius: 12px;"><i class="bi bi-bag me-2"></i>MY ORDERS</a>
            </div>
        </div>
        <div class="col-md-5 d-flex gap-3 align-items-center">
            <div class="stat-box flex-fill">
                <span class="stat-label">Orders</span>
                <span class="stat-value"><?php echo $stats['total'] ?? 0; ?></span>
            </div>
            <div class="stat-box flex-fill">
                <span class="stat-label">Spent</span>
                <span class="stat-value">₹<?php echo number_format($stats['spent'] ?? 0); ?></span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="glass-card">
                <h6 class="opacity-50 fw-bold mb-4">LOGISTICS_STREAM</h6>
                <div class="logistics-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr><th>REF ID</th><th>IDENTITY</th><th>STATUS</th><th class="text-end">ACTION</th></tr>
                            </thead>
                            <tbody>
                                <?php if($orders && mysqli_num_rows($orders) > 0): 
                                    while($row = mysqli_fetch_assoc($orders)): 
                                ?>
                                <tr>
                                    <td class="fw-bold text-cyan">#ORD-<?php echo $row['id']; ?></td>
                                    <td>Medical Supplies Batch</td>
                                    <td><span class="badge-status"><?php echo strtoupper($row['status']); ?></span></td>
                                    <td class="text-end">
                                        <a href="order_details.php?id=<?php echo $row['id']; ?>" class="text-cyan"><i class="bi bi-eye-fill fs-5"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="4" class="text-center opacity-50 py-5">No active dispatches found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="glass-card">
                <h6 class="text-cyan fw-bold mb-4"><i class="bi bi-heart-pulse me-2"></i>HEALTH_ADVISOR</h6>
                <div class="row g-2">
                    <div class="col-6"><div class="health-mini-box"><small class="stat-label" style="font-size:0.6rem;">Age</small><div class="fw-bold fs-5"><?php echo $age_val; ?></div></div></div>
                    <div class="col-6"><div class="health-mini-box"><small class="stat-label" style="font-size:0.6rem;">BMI</small><div class="fw-bold fs-5 text-cyan"><?php echo $bmi_val; ?></div></div></div>
                    <div class="col-6"><div class="health-mini-box"><small class="stat-label" style="font-size:0.6rem;">Gender</small><div class="fw-bold fs-5"><?php echo $u_gender; ?></div></div></div>
                    <div class="col-6"><div class="health-mini-box"><small class="stat-label" style="font-size:0.6rem;">Blood</small><div class="fw-bold fs-5 text-danger"><?php echo strtoupper($u_bg); ?></div></div></div>
                    <div class="col-12"><div class="health-mini-box"><small class="stat-label" style="font-size:0.6rem;">Condition</small><div class="fw-bold" style="font-size: 0.75rem;"><?php echo $health_status; ?></div></div></div>
                </div>
                <div class="mt-4 p-3 rounded border border-info border-opacity-25" style="background: rgba(45, 212, 191, 0.05);">
                    <p class="small mb-1 fw-bold text-cyan"><i class="bi bi-droplet-fill me-2"></i>TIPS:</p>
                    <p class="small mb-0 opacity-75"><?php echo $health_msg; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content custom-modal-content">
            <div class="row g-0">
                <div class="col-md-4 modal-sidebar">
                    <button class="nav-btn active" onclick="showTab('details-tab', this)"><i class="bi bi-person"></i> DETAILS</button>
                    <button class="nav-btn" onclick="showTab('update-tab', this)"><i class="bi bi-pencil-square"></i> PROFILE UPDATE</button>
                </div>
                <div class="col-md-8 p-5">
                    <div id="details-tab" class="tab-content active">
                        <h4 class="fw-bold mb-4">Patient Profile</h4>
                        <div class="mb-3">
                            <small class="text-uppercase opacity-50 fw-bold" style="font-size: 0.7rem;">Username</small>
                            <h3 class="fw-bold"><?php echo $display_name; ?></h3>
                        </div>
                        <div class="mb-3">
                            <small class="text-uppercase opacity-50 fw-bold" style="font-size: 0.7rem;">Email</small>
                            <p class="fs-6 text-cyan mb-1"><?php echo $user_email; ?></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-uppercase opacity-50 fw-bold" style="font-size: 0.7rem;">Phone</small>
                            <p class="fs-6 mb-1"><?php echo $u_phone; ?></p>
                        </div>
                        <div class="mb-3">
                            <small class="text-uppercase opacity-50 fw-bold" style="font-size: 0.7rem;">Gender</small>
                            <p class="fs-6 mb-1"><?php echo $u_gender; ?></p>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-3" data-bs-dismiss="modal">Close Overlay</button>
                    </div>

                    <div id="update-tab" class="tab-content">
                        <h4 class="fw-bold mb-4 text-cyan">Update Health Metrics</h4>
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6"><label class="small opacity-50">First Name</label><input type="text" name="first_name" class="form-control form-control-custom" value="<?php echo $user_data['first_name'] ?? ''; ?>"></div>
                                <div class="col-md-6"><label class="small opacity-50">Last Name</label><input type="text" name="last_name" class="form-control form-control-custom" value="<?php echo $user_data['last_name'] ?? ''; ?>"></div>
                                <div class="col-md-6"><label class="small opacity-50">Phone Number</label><input type="text" name="phone" class="form-control form-control-custom" value="<?php echo $u_phone; ?>"></div>
                                <div class="col-md-6">
                                    <label class="small opacity-50">Gender</label>
                                    <select name="gender" class="form-control form-control-custom">
                                        <option value="Male" <?php if($u_gender=='Male') echo 'selected'; ?>>Male</option>
                                        <option value="Female" <?php if($u_gender=='Female') echo 'selected'; ?>>Female</option>
                                        <option value="Other" <?php if($u_gender=='Other') echo 'selected'; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="small opacity-50 me-3">Height Input Mode:</label>
                                    <select name="height_type" id="height_unit" class="form-control-custom" style="padding: 2px 10px; font-size: 0.8rem;" onchange="switchHeightUI()">
                                        <option value="cm">Centimeters (cm)</option>
                                        <option value="ft">Feet/Inches (ft/in)</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="cm_box">
                                    <label class="small opacity-50">Height (cm)</label>
                                    <input type="number" step="0.1" name="height" class="form-control form-control-custom" value="<?php echo $u_height; ?>">
                                </div>
                                <div class="col-md-4 d-none" id="ft_box">
                                    <label class="small opacity-50">Feet / Inches</label>
                                    <div class="d-flex gap-1">
                                        <input type="number" name="height_ft" placeholder="ft" class="form-control form-control-custom">
                                        <input type="number" name="height_in" placeholder="in" class="form-control form-control-custom">
                                    </div>
                                </div>
                                <div class="col-md-4"><label class="small opacity-50">Weight (kg)</label><input type="number" step="0.1" name="weight" class="form-control form-control-custom" value="<?php echo $u_weight; ?>"></div>
                                <div class="col-md-4"><label class="small opacity-50">Blood Group</label><input type="text" name="blood_group" class="form-control form-control-custom" value="<?php echo $u_bg; ?>"></div>
                                <div class="col-12"><label class="small opacity-50">Birthdate</label><input type="date" name="birthdate" class="form-control form-control-custom" value="<?php echo $u_dob; ?>"></div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-info w-100 mt-4 fw-bold text-dark">SYNC DATA</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleTheme() {
        const theme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
    }
    function showTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');
    }
    function switchHeightUI() {
        const mode = document.getElementById('height_unit').value;
        const cmBox = document.getElementById('cm_box');
        const ftBox = document.getElementById('ft_box');
        if(mode === 'ft') {
            cmBox.classList.add('d-none');
            ftBox.classList.remove('d-none');
        } else {
            cmBox.classList.remove('d-none');
            ftBox.classList.add('d-none');
        }
    }
</script>
</body>
</html>