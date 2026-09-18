<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Only Admin (Role 1)
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. FETCH MESSAGES: Joined with User Identity for a complete profile
$query = "SELECT contact_messages.*, users.username, users.email 
          FROM contact_messages 
          JOIN users ON contact_messages.user_id = users.id 
          ORDER BY 
            CASE WHEN status = 'Pending' THEN 1 ELSE 2 END, 
            created_at DESC";
$result = mysqli_query($conn, $query);
$count = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Communication Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono&display=swap');
        
        :root {
            --mims-bg: #0b0c10;
            --rose-gold: #c5a1a1;
            --card-bg: #111216;
            --glass-border: rgba(197, 161, 161, 0.1);
            --emerald: #10b981;
            --crimson: #ff4b5c;
        }

        body { 
            background-color: var(--mims-bg); 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #ffffff; 
            min-height: 100vh;
            background-image: radial-gradient(circle at 90% 10%, rgba(197, 161, 161, 0.03) 0%, transparent 40%);
        }
        
        .msg-card { 
            border: 1px solid var(--glass-border); 
            border-radius: 32px; 
            background: var(--card-bg); 
            margin-bottom: 25px; 
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .msg-card:hover { 
            border-color: var(--rose-gold); 
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5); 
        }

        .avatar-box {
            width: 60px; height: 60px; border-radius: 20px;
            background: rgba(197, 161, 161, 0.05); 
            display: flex; align-items: center;
            justify-content: center; font-weight: 800; 
            color: var(--rose-gold); 
            border: 1px solid var(--glass-border);
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.4rem;
        }

        .status-badge { 
            font-size: 0.6rem; 
            padding: 6px 14px; 
            border-radius: 50px; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
        }
        .status-pending { background: rgba(255, 75, 92, 0.1); color: var(--crimson); border: 1px solid rgba(255, 75, 92, 0.2); }
        .status-resolved { background: rgba(16, 185, 129, 0.1); color: var(--emerald); border: 1px solid rgba(16, 185, 129, 0.2); }

        .content-area { 
            background: rgba(255,255,255,0.02); 
            border-radius: 24px; 
            padding: 30px; 
            border-left: 4px solid var(--rose-gold);
            color: #cbd5e1;
            line-height: 1.8;
            font-size: 0.95rem;
            position: relative;
        }

        .btn-reply { 
            background: var(--rose-gold); color: #000; border: none; 
            border-radius: 14px; padding: 12px 24px; font-weight: 800; 
            font-size: 0.75rem; transition: 0.3s; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .btn-reply:hover { background: #fff; color: #000; transform: scale(1.05); }

        .mono { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--rose-gold); }
        .subject-line { color: #f8fafc; font-weight: 800; letter-spacing: -0.5px; font-size: 1.15rem; }
        
        .thread-action {
            text-decoration: none;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 1px;
            transition: 0.3s;
        }
    </style>
</head>
<body class="py-5">
    <div class="container animate__animated animate__fadeIn">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-5 px-3 gap-3">
            <div>
                <h6 class="text-uppercase mb-1 fw-800" style="letter-spacing: 5px; color: var(--rose-gold); font-size: 0.7rem;">Inbound Data Stream</h6>
                <h2 class="fw-800 mb-0" style="letter-spacing: -1.5px; font-size: 2.5rem;">Communication <span class="text-white-50">Terminal</span></h2>
            </div>
            <div class="d-flex gap-4 align-items-center">
                <div class="text-end d-none d-md-block">
                    <small class="text-white-50 d-block mono">BUFFER_STATUS</small>
                    <span class="fw-bold text-white"><?php echo $count; ?> Verified Threads</span>
                </div>
                <a href="dashboard.php" class="btn btn-outline-light rounded-pill px-4 fw-bold btn-sm" style="border-color: var(--glass-border);">
                    <i class="bi bi-arrow-left-short me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <?php if($count > 0): ?>
            <?php while($msg = mysqli_fetch_assoc($result)): 
                // Enhanced Urgency Logic
                $is_urgent = (stripos($msg['subject'], 'urgent') !== false || 
                             stripos($msg['message'], 'stock') !== false || 
                             stripos($msg['message'], 'expired') !== false);
            ?>
                <div class="card msg-card p-4 animate__animated animate__fadeInUp">
                    <div class="row g-0">
                        <div class="col-md-3 border-md-end border-secondary" style="--bs-border-opacity: .1;">
                            <div class="pe-md-4 h-100 d-flex flex-column">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="avatar-box me-3">
                                        <?php echo strtoupper(substr($msg['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h6 class="fw-800 mb-0 text-white"><?php echo htmlspecialchars($msg['username']); ?></h6>
                                        <span class="status-badge <?php echo ($msg['status'] == 'Resolved') ? 'status-resolved' : 'status-pending'; ?>">
                                            <?php echo $msg['status'] ?? 'Active'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="mono mb-4 flex-grow-1" style="line-height: 2.2;">
                                    <div class="text-truncate opacity-75"><i class="bi bi-fingerprint me-2"></i><?php echo str_pad($msg['id'], 5, '0', STR_PAD_LEFT); ?></div>
                                    <div class="text-truncate opacity-75"><i class="bi bi-envelope-at me-2"></i><?php echo htmlspecialchars($msg['email']); ?></div>
                                    <div class="opacity-75"><i class="bi bi-calendar3 me-2"></i><?php echo date('d M, Y', strtotime($msg['created_at'])); ?></div>
                                </div>

                                <div class="pt-3 border-top border-secondary" style="--bs-border-opacity: .1;">
                                    <?php if(($msg['status'] ?? 'Pending') == 'Pending'): ?>
                                        <a href="update_msg_status.php?id=<?php echo $msg['id']; ?>&status=Resolved" class="thread-action text-success">
                                            <i class="bi bi-check-circle-fill me-2"></i>Mark Resolved
                                        </a>
                                    <?php else: ?>
                                        <a href="update_msg_status.php?id=<?php echo $msg['id']; ?>&status=Pending" class="thread-action text-white-50">
                                            <i class="bi bi-arrow-counterclockwise me-2"></i>Reopen Thread
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9 ps-md-4 mt-4 mt-md-0">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="subject-line mb-0">
                                    <?php if($is_urgent): ?>
                                        <span class="badge bg-danger text-white me-2 animate__animated animate__flash animate__infinite" style="font-size: 0.55rem; vertical-align: middle; padding: 5px 8px;">URGENT_PRIORITY</span>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($msg['subject']); ?>
                                </h5>
                                <button class="btn btn-reply text-nowrap" type="button" data-bs-toggle="collapse" data-bs-target="#replyBox<?php echo $msg['id']; ?>">
                                    <i class="bi bi-chat-right-dots-fill me-2"></i>Respond
                                </button>
                            </div>

                            <div class="content-area mb-3">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                            </div>

                            <div class="collapse" id="replyBox<?php echo $msg['id']; ?>">
                                <div class="p-4 rounded-4" style="background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);">
                                    <form action="reply_message.php" method="POST">
                                        <input type="hidden" name="msg_id" value="<?php echo $msg['id']; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <label class="mono">TERMINAL_ENCRYPTION_ACTIVE</label>
                                            <i class="bi bi-shield-lock text-success"></i>
                                        </div>
                                        <textarea name="reply_text" class="form-control bg-transparent text-white border-secondary mb-3" rows="4" placeholder="Compose your secure response..." required style="border-radius: 18px; border-color: rgba(255,255,255,0.1) !important;"></textarea>
                                        <div class="d-flex justify-content-end gap-3">
                                            <button type="button" class="btn btn-link text-white-50 text-decoration-none btn-sm fw-bold" data-bs-toggle="collapse" data-bs-target="#replyBox<?php echo $msg['id']; ?>">Discard</button>
                                            <button type="submit" class="btn btn-reply px-5">
                                                Transmit <i class="bi bi-send-fill ms-2" style="font-size: 0.8rem;"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5 mt-5">
                <div class="mb-4 opacity-10">
                    <i class="bi bi-chat-square-dots" style="font-size: 6rem;"></i>
                </div>
                <h4 class="fw-800 text-white-50">NO_INBOUND_QUERIES</h4>
                <p class="mono opacity-50">All communication channels are currently clear.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>