<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY: Admin Authentication
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. HANDLE REPLY SUBMISSION
if(isset($_POST['submit_reply'])) {
    $msg_id = mysqli_real_escape_string($conn, $_POST['msg_id']);
    $reply_text = mysqli_real_escape_string($conn, $_POST['reply_text']);
    
    // Update the message with the admin's reply and mark as solved
    $update_sql = "UPDATE support_messages SET admin_reply = '$reply_text', status = 'solved' WHERE id = '$msg_id'";
    if(mysqli_query($conn, $update_sql)) {
        header("Location: messages.php?msg=Transmission Synchronized");
        exit();
    }
}

// 3. DATA QUERY: Get messages, user details, and Order Status
$query = "SELECT m.*, u.username, u.email, o.status as order_current_status 
          FROM support_messages m 
          LEFT JOIN users u ON m.user_id = u.id 
          LEFT JOIN orders o ON m.order_id = o.id
          ORDER BY m.created_at DESC";
$result = mysqli_query($conn, $query);
$total_msgs = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIMS | Neural Communications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --cyan: #2DD4BF; 
            --bg: #030712; 
            --card-bg: #0b0e14; 
            --border: rgba(45, 212, 191, 0.15); 
        }

        body { 
            background: var(--bg); 
            color: #fff; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: radial-gradient(circle at 100% 0%, rgba(45, 212, 191, 0.05) 0%, transparent 50%);
            min-height: 100vh;
        }

        .comm-header { 
            background: var(--card-bg); 
            border-radius: 28px; 
            padding: 40px; 
            margin-top: 30px; 
            border: 1px solid var(--border);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .msg-card { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 24px; 
            padding: 25px; 
            margin-bottom: 20px; 
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        }

        .msg-card:hover { 
            border-color: var(--cyan); 
            transform: translateX(10px);
            background: rgba(45, 212, 191, 0.02);
        }

        .msg-icon { 
            width: 50px; height: 50px; 
            background: rgba(45, 212, 191, 0.1); 
            border-radius: 14px; 
            display: flex; align-items: center; justify-content: center; 
            color: var(--cyan); font-size: 1.5rem; margin-right: 18px; 
        }

        .badge-order { 
            background: rgba(45, 212, 191, 0.1); 
            color: var(--cyan); 
            border: 1px solid var(--cyan); 
            font-size: 0.65rem; 
            padding: 4px 12px; 
            border-radius: 50px; 
            font-weight: 800;
        }

        .status-indicator {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 900;
            padding: 3px 10px;
            border-radius: 4px;
            background: #1a1d23;
        }

        .reply-box {
            background: rgba(255, 255, 255, 0.03); 
            border-left: 3px solid var(--cyan);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .modal-content { 
            background: #0b0e14; 
            border: 1px solid var(--cyan); 
            color: white; 
            border-radius: 28px; 
            padding: 20px;
        }

        .form-control { 
            background: #000; 
            border: 1px solid var(--border); 
            color: white; 
            border-radius: 12px;
        }

        .form-control:focus { 
            background: #000; color: white; border-color: var(--cyan); box-shadow: 0 0 15px rgba(45, 212, 191, 0.1); 
        }

        .btn-cyan { background: var(--cyan); color: #000; font-weight: 800; border: none; }
        .btn-cyan:hover { background: #24b09e; color: #000; transform: translateY(-1px); }
    </style>
</head>
<body>

<div class="container pb-5">
    <div class="comm-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <span class="badge mb-2 fw-bold" style="background: var(--cyan); color: #000; font-size: 0.6rem;">NEURAL INBOX</span>
            <h1 style="font-family: 'Outfit'; font-weight: 900;" class="m-0 text-white">Support Center</h1>
            <p class="text-muted small m-0">Synchronized with Patient Terminals</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-info rounded-pill px-4 fw-bold" style="border-color: var(--cyan); color: var(--cyan);">
            <i class="bi bi-cpu me-2"></i>DASHBOARD
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-pill border-0 shadow-sm mb-4" 
             style="background: rgba(45, 212, 191, 0.1); color: var(--cyan);">
            <i class="bi bi-check2-all me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-10 mx-auto">
            <?php if($total_msgs > 0): ?>
                <?php while($msg = mysqli_fetch_assoc($result)): 
                    $is_order_issue = !empty($msg['order_id']);
                ?>
                    <div class="msg-card d-flex align-items-start shadow-sm">
                        <div class="msg-icon"><i class="bi bi-chat-right-dots-fill"></i></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-0" style="color: var(--cyan)"><?php echo htmlspecialchars($msg['username'] ?? 'Unknown User'); ?></h6>
                                    <small class="text-muted"><?php echo htmlspecialchars($msg['email']); ?></small>
                                </div>
                                <div class="text-end">
                                    <small class="opacity-50 fw-bold d-block" style="font-size: 0.7rem;"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></small>
                                    <small class="opacity-50 fw-bold" style="font-size: 0.65rem;"><?php echo date('H:i A', strtotime($msg['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <div class="my-3 d-flex align-items-center gap-2">
                                <?php if($is_order_issue): ?>
                                    <span class="badge-order">REF: #ORD-<?php echo $msg['order_id']; ?></span>
                                    <span class="status-indicator text-cyan border border-info border-opacity-25">
                                        LIVE STATUS: <?php echo strtoupper($msg['order_current_status'] ?? 'N/A'); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-dark text-secondary fw-bold" style="font-size: 0.65rem;">ID: #MS-<?php echo $msg['id']; ?></span>
                            </div>

                            <p class="opacity-75 mb-4" style="font-size: 0.95rem; line-height: 1.6; font-style: italic;">
                                "<?php echo nl2br(htmlspecialchars($msg['message'])); ?>"
                            </p>

                            <?php if($msg['admin_reply']): ?>
                                <div class="reply-box mb-4">
                                    <small class="fw-bold d-block mb-2" style="color: var(--cyan); letter-spacing: 1px;">LAST RESPONSE:</small>
                                    <span class="small opacity-75">"<?php echo htmlspecialchars($msg['admin_reply']); ?>"</span>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-cyan rounded-pill px-4" 
                                        onclick="openReplyModal(<?php echo $msg['id']; ?>, '<?php echo addslashes($msg['message']); ?>')">
                                    <i class="bi bi-reply-fill me-1"></i> <?php echo $msg['admin_reply'] ? 'Update Response' : 'Reply Now'; ?>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirmDelete(<?php echo $msg['id']; ?>)">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5" style="background: var(--card-bg); border: 1px dashed var(--border); border-radius: 30px;">
                    <i class="bi bi-shield-check opacity-25" style="font-size: 5rem; color: var(--cyan);"></i>
                    <h5 class="mt-4 opacity-50 fw-bold">No pending communications in queue.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content shadow-lg" method="POST">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-900" style="font-family: 'Outfit';">NEURAL REPLY</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="msg_id" id="modal_msg_id">
                <div class="mb-4">
                    <label class="small opacity-50 fw-bold mb-2">PATIENT INPUT:</label>
                    <div id="modal_user_msg" class="p-3 border border-secondary rounded small opacity-75 bg-black" style="font-style: italic;"></div>
                </div>
                <div class="mb-3">
                    <label class="small fw-bold mb-2" style="color: var(--cyan)">TRANSMISSION CONTENT:</label>
                    <textarea name="reply_text" class="form-control" rows="5" required placeholder="Type your response to the patient..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="submit" name="submit_reply" class="btn btn-cyan w-100 rounded-pill py-3">SEND TRANSMISSION</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openReplyModal(id, text) {
    document.getElementById('modal_msg_id').value = id;
    document.getElementById('modal_user_msg').innerText = text;
    new bootstrap.Modal(document.getElementById('replyModal')).show();
}

function confirmDelete(id) {
    if(confirm('Warning: Permanently delete this message thread?')) {
        window.location.href = 'delete_message.php?id=' + id;
    }
}
</script>
</body>
</html>