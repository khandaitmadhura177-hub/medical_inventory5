<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Authentication Guard (Level 1 Only)
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

// 2. DELETE LOGIC: Protected against self-deletion & system protection
if(isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $current_admin = $_SESSION['user_id'];
    
    // Prevention of self-deletion
    if($del_id == $current_admin) {
        header("Location: manage_user.php?status=error&msg=self_deletion_blocked");
        exit();
    }

    // Safety: Ensure we don't delete the absolute master admin (ID 1) if desired
    $delete_query = "DELETE FROM users WHERE id = '$del_id' AND id != 1"; 
    
    if(mysqli_query($conn, $delete_query)) {
        header("Location: manage_user.php?status=success&msg=user_purged");
    } else {
        header("Location: manage_user.php?status=error&msg=purge_failed");
    }
    exit();
}

// 3. FETCH USERS: Sorted by Admin privilege first
$query = "SELECT id, username, email, role, created_at FROM users ORDER BY role DESC, username ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Identity Access Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-bg: #0b0c10;
            --rose-gold: #c5a1a1;
            --card-bg: #111216;
            --glass-border: rgba(197, 161, 161, 0.15);
        }

        body { 
            background-color: var(--mims-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #ffffff; 
            background-image: radial-gradient(circle at top right, rgba(197, 161, 161, 0.05), transparent);
        }

        .user-card { 
            border: 1px solid var(--glass-border); 
            border-radius: 35px; 
            background: var(--card-bg);
            box-shadow: 0 40px 80px rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
        }

        .role-badge { 
            padding: 6px 14px; 
            border-radius: 10px; 
            font-size: 0.65rem; 
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .badge-admin { 
            background: rgba(197, 161, 161, 0.1); 
            color: var(--rose-gold); 
            border: 1px solid var(--rose-gold); 
        }
        
        .badge-customer { 
            background: rgba(255,255,255,0.03); 
            color: #64748b; 
            border: 1px solid rgba(255,255,255,0.1); 
        }

        .avatar-circle { 
            width: 48px; 
            height: 48px; 
            background: linear-gradient(135deg, #1a1b1f 0%, #111216 100%); 
            color: var(--rose-gold); 
            border: 1px solid var(--glass-border);
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 800; 
            font-family: 'JetBrains Mono', monospace;
        }

        .table { color: #e2e8f0; margin-bottom: 0; }
        .table thead th { 
            border: none; 
            color: var(--rose-gold); 
            font-size: 0.65rem; 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            padding: 25px 20px; 
            opacity: 0.8;
        }

        .search-container {
            position: relative;
            max-width: 400px;
        }

        .search-input {
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 10px 15px 10px 40px;
            color: white;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .search-input:focus {
            background: rgba(255,255,255,0.06);
            border-color: var(--rose-gold);
            box-shadow: none;
            color: white;
        }

        .btn-action-delete {
            color: rgba(255, 255, 255, 0.2);
            transition: 0.3s;
            padding: 8px;
            border-radius: 10px;
        }
        
        .btn-action-delete:hover { 
            color: #ff4b5c; 
            background: rgba(255, 75, 92, 0.1);
        }

        .status-pill {
            background: rgba(197, 161, 161, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--rose-gold);
            font-size: 0.65rem;
            padding: 5px 12px;
            border-radius: 50px;
            text-transform: uppercase;
            font-weight: 700;
        }

        .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; }
    </style>
</head>
<body class="py-5">

<div class="container animate__animated animate__fadeIn">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
        <div>
            <h2 class="fw-800 mb-1" style="letter-spacing: -1.5px;">Identity <span style="color: var(--rose-gold);">Access</span></h2>
            <p class="text-white-50 small mb-0">System operator oversight and identity synchronization</p>
        </div>
        <div class="d-flex gap-3">
            <div class="search-container">
                <i class="bi bi-search position-absolute text-white-50" style="left: 15px; top: 12px;"></i>
                <input type="text" id="userSearch" class="form-control search-input" placeholder="Filter identities...">
            </div>
            <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold opacity-75 btn-sm d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Dashboard
            </a>
        </div>
    </div>

    <?php if(isset($_GET['status']) && $_GET['status'] == 'error'): ?>
        <div class="alert bg-danger bg-opacity-10 text-danger border-danger border-opacity-25 rounded-4 mb-4 animate__animated animate__shakeX">
            <i class="bi bi-shield-lock-fill me-2"></i> <strong>Protocol Breach:</strong> Self-deletion or master-account purge is restricted.
        </div>
    <?php endif; ?>

    <div class="card user-card p-4">
        <div class="table-responsive">
            <table class="table align-middle" id="userTable">
                <thead>
                    <tr>
                        <th>Operator Identity</th>
                        <th>Communication Node</th>
                        <th>Access Level</th>
                        <th>Entry Timestamp</th>
                        <th class="text-end">Command</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="border-bottom border-secondary" style="--bs-border-opacity: .05;">
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <?php echo strtoupper(substr($row['username'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="fw-bold text-white"><?php echo htmlspecialchars($row['username']); ?></div>
                                    <div class="mono text-white-50" style="font-size: 0.6rem;">UID_<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-white-50 small"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <?php if($row['role'] == 1): ?>
                                <span class="role-badge badge-admin">
                                    <i class="bi bi-cpu-fill me-1"></i> Root Access
                                </span>
                            <?php else: ?>
                                <span class="role-badge badge-customer">
                                    <i class="bi bi-person-badge me-1"></i> Staff Node
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-white-50 mono" style="font-size: 0.7rem;">
                            <?php echo date('Y-m-d / H:i', strtotime($row['created_at'])); ?>
                        </td>
                        <td class="text-end">
                            <?php if($row['id'] != $_SESSION['user_id']): ?>
                                <a href="manage_user.php?delete_id=<?php echo $row['id']; ?>" 
                                   class="btn-action-delete" 
                                   onclick="return confirm('DANGER: Permanently purge this identity from the central registry?')">
                                    <i class="bi bi-trash3-fill fs-6"></i>
                                </a>
                            <?php else: ?>
                                <span class="status-pill">
                                    <i class="bi bi-broadcast me-1"></i> Current Session
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Real-time Identity Filtering
    document.getElementById('userSearch').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#userTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>