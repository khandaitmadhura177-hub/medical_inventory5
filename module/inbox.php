<?php
session_start();
include('../config/db_connect.php');

// Security Check: Admin Access Only
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

// Fetch messages joined with user info
$query = "SELECT contact_messages.*, users.username, users.email 
          FROM contact_messages 
          JOIN users ON contact_messages.user_id = users.id 
          ORDER BY contact_messages.created_at DESC";
$result = mysqli_query($conn, $query);

// Count for Stats Bar
$total_count = mysqli_num_rows($result);
$res_count = mysqli_query($conn, "SELECT COUNT(*) as pending FROM contact_messages WHERE status = 'Pending'");
$pending_count = mysqli_fetch_assoc($res_count)['pending'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Comm Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --bg: #0b0c10;
            --rose-gold: #b76e79;
            --card-bg: #14161a;
            --input-bg: #1c1e24;
            --status-pending: #fbbf24;
            --status-resolved: #10b981;
        }

        body { 
            background-color: var(--bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #e2e8f0;
            min-height: 100vh;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .msg-card { 
            border: 1px solid rgba(183, 110, 121, 0.08); 
            border-radius: 35px; 
            background: var(--card-bg); 
            margin-bottom: 30px; 
            transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            position: relative;
        }
        
        .msg-card:hover { 
            transform: translateY(-5px) scale(1.01); 
            border-color: var(--rose-gold);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5); 
        }
        
        .status-badge { 
            font-size: 0.65rem; 
            font-weight: 800; 
            letter-spacing: 1.5px; 
            padding: 10px 20px; 
            border-radius: 12px;
        }

        .badge-pending { background: rgba(251, 191, 36, 0.1); color: var(--status-pending); border: 1px solid var(--status-pending); }
        .badge-resolved { background: rgba(16, 185, 129, 0.1); color: var(--status-resolved); border: 1px solid var(--status-resolved); }
        
        .stats-pill { 
            background: rgba(255,255,255,0.03); 
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 20px; 
            padding: 10px 20px; 
        }

        .avatar-box { 
            width: 65px; 
            height: 65px; 
            background: linear-gradient(135deg, var(--rose-gold), #6d3a42); 
            border-radius: 22px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .message-content {
            background: var(--input-bg);
            border-radius: 25px;
            padding: 25px;
            border: 1px solid rgba(255,255,255,0.02);
            font-size: 0.95rem;
            color: #cbd5e1;
        }

        .admin-reply-sector {
            background: rgba(183, 110, 121, 0.03);
            border-left: 3px solid var(--rose-gold);
            border-radius: 0 20px 20px 0;
            padding: 20px;
            margin-top: 20px;
        }

        .btn-action {
            border-radius: 15px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .modal-content {
            background: #111216;
            border: 1px solid var(--rose-gold);
            border-radius: 40px;
            padding: 20px;
        }

        .form-control {
            background: #1a1c22;
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 18px;
            padding: 15px;
        }

        .form-control:focus {
            background: #202229;
            border-color: var(--rose-gold);
            color: white;
            box-shadow: 0 0 20px rgba(183, 110, 121, 0.1);
        }
    </style>
</head>
<body class="py-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-4">
            <div class="animate__animated animate__fadeInLeft">
                <h2 class="fw-800 mb-1" style="letter-spacing: -1px;">Intelligence Hub</h2>
                <p class="text-white-50 small mb-0"><i class="bi bi-broadcast me-2 text-primary"></i> Monitoring <?php echo $total_count; ?> active communication nodes</p>
            </div>
            <div class="d-flex gap-3 align-items-center animate__animated animate__fadeInRight">
                <div class="stats-pill">
                    <span class="text-white-50 small mono">PENDING_QUE:</span> 
                    <span class="fw-bold ms-2" style="color: var(--status-pending);"><?php echo $pending_count; ?></span>
                </div>
                <a href="../admin/dashboard.php" class="btn btn-outline-light rounded-pill px-4 btn-sm fw-bold">
                    <i class="bi bi-cpu-fill me-2"></i>Terminal
                </a>
            </div>
        </div>

        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($msg = mysqli_fetch_assoc($result)): ?>
            <div class="card msg-card animate__animated animate__fadeInUp">
                <div class="card-body p-4 p-lg-5">
                    <div class="row align-items-start">
                        <div class="col-md-auto mb-4 mb-md-0 text-center">
                            <div class="avatar-box mx-auto">
                                <?php echo strtoupper(substr($msg['username'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="col">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                <div>
                                    <h4 class="fw-800 mb-1 text-white"><?php echo htmlspecialchars($msg['username']); ?></h4>
                                    <div class="mono text-white-50 small">REF_ID: #<?php echo str_pad($msg['id'], 5, '0', STR_PAD_LEFT); ?> | <?php echo htmlspecialchars($msg['email']); ?></div>
                                </div>
                                <?php 
                                    $is_pending = ($msg['status'] ?? 'Pending') == 'Pending';
                                    $badge_class = $is_pending ? 'badge-pending' : 'badge-resolved';
                                    echo "<span class='status-badge $badge_class'>".($msg['status'] ?? 'Pending')."</span>";
                                ?>
                            </div>

                            <div class="mb-4">
                                <div class="text-rose-gold fw-bold mb-3 mono" style="font-size: 0.8rem; color: var(--rose-gold);">
                                    SUBJECT >> <?php echo htmlspecialchars($msg['subject']); ?>
                                </div>
                                <div class="message-content shadow-inner">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                            </div>

                            <?php if(!empty($msg['admin_reply'])): ?>
                                <div class="admin-reply-sector animate__animated animate__fadeIn">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-shield-check me-2 text-rose-gold"></i>
                                        <span class="mono fw-bold text-white-50 small" style="letter-spacing: 1px;">ADMIN_RESPONSE</span>
                                    </div>
                                    <p class="mb-0 text-white opacity-75 fst-italic">"<?php echo htmlspecialchars($msg['admin_reply']); ?>"</p>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center pt-5 mt-4 border-top border-secondary border-opacity-25">
                                <div class="d-flex gap-3">
                                    <button class="btn btn-action shadow-sm" style="background: var(--rose-gold); color: white;" data-bs-toggle="modal" data-bs-target="#replyModal<?php echo $msg['id']; ?>">
                                        <i class="bi bi-reply-fill me-2"></i>Transmit Reply
                                    </button>

                                    <a href="update_msg_status.php?id=<?php echo $msg['id']; ?>&status=<?php echo $is_pending ? 'Resolved' : 'Pending'; ?>" 
                                       class="btn btn-outline-secondary btn-action px-4">
                                        Mark <?php echo $is_pending ? 'Resolved' : 'Open'; ?>
                                    </a>
                                </div>
                                
                                <a href="delete_message.php?id=<?php echo $msg['id']; ?>" 
                                   class="btn btn-link text-danger text-decoration-none opacity-50 hover-opacity-100" 
                                   onclick="return confirm('Confirm permanent deletion of this record?')">
                                    <i class="bi bi-trash3-fill fs-5"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="replyModal<?php echo $msg['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="reply_message.php" method="POST" class="w-100">
                        <div class="modal-content">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="fw-800 text-white">Reply to <?php echo htmlspecialchars($msg['username']); ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                <label class="mono text-white-50 small mb-2">MESSAGE_BODY</label>
                                <textarea name="reply_text" class="form-control" rows="5" placeholder="Formulate clinical/admin response..." required><?php echo $msg['admin_reply'] ?? ''; ?></textarea>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="submit" class="btn btn-action w-100 py-3" style="background: var(--rose-gold); color: white; border: none;">
                                    SEND TRANSMISSION
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5 rounded-5 mt-5 animate__animated animate__zoomIn" style="background: var(--card-bg); border: 1px dashed rgba(183,110,121,0.2);">
                <i class="bi bi-envelope-check display-1 text-white-50 opacity-10"></i>
                <h4 class="mt-4 fw-800">No Intelligence Required</h4>
                <p class="text-white-50 mono small">Communication channels: OPTIMAL</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>