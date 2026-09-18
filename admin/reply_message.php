<?php
session_start();
include('../config/db_connect.php');

// 1. SECURITY CHECK
if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// 2. HANDLE THE FORM SUBMISSION (Logic Moved from process_reply.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $message_id = (int)$_POST['message_id'];
    $reply_text = mysqli_real_escape_string($conn, $_POST['admin_reply']);

    $update_sql = "UPDATE support_messages 
                   SET admin_reply = '$reply_text', 
                       status = 'resolved' 
                   WHERE id = $message_id";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: messages.php?status=replied");
        exit();
    }
}

// 3. FETCH DATA FOR THE UI
if(!isset($_GET['id'])) {
    header("Location: messages.php");
    exit();
}

$msg_id = (int)$_GET['id'];

$query = "SELECT m.*, u.username, u.email 
          FROM support_messages m 
          LEFT JOIN users u ON m.user_id = u.id 
          WHERE m.id = $msg_id";
          
$result = mysqli_query($conn, $query);
$msg = mysqli_fetch_assoc($result);

if (!$msg) {
    header("Location: messages.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Issue Resolution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        
        :root { 
            --cyan: #2DD4BF; 
            --bg-deep: #030712; 
            --card-bg: #0b0e14; 
            --border: rgba(45, 212, 191, 0.15); 
        }

        body { 
            background: var(--bg-deep) !important; 
            color: #fff; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: radial-gradient(circle at 50% 0%, rgba(45, 212, 191, 0.05) 0%, transparent 50%);
            min-height: 100vh;
        }

        .reply-panel { 
            background: var(--card-bg); 
            border: 1px solid var(--border); 
            border-radius: 30px; 
            padding: 40px; 
            margin-top: 50px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }

        .original-msg { 
            background: rgba(45, 212, 191, 0.03); 
            border-radius: 20px; 
            padding: 25px; 
            border-left: 4px solid var(--cyan); 
        }

        textarea { 
            background: rgba(0, 0, 0, 0.3) !important; 
            color: white !important; 
            border: 1px solid var(--border) !important; 
            border-radius: 18px !important; 
            padding: 20px !important;
        }

        textarea:focus { 
            border-color: var(--cyan) !important; 
            box-shadow: 0 0 15px rgba(45, 212, 191, 0.1) !important; 
        }

        .text-cyan { color: var(--cyan) !important; }
        .fw-900 { font-weight: 900; }
        
        .btn-cyan {
            background: var(--cyan);
            color: #000;
            font-weight: 800;
            border-radius: 50px;
            padding: 15px;
            transition: 0.3s;
            border: none;
        }

        .btn-cyan:hover {
            background: #24b09e;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(45, 212, 191, 0.3);
        }
    </style>
</head>
<body>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="reply-panel shadow-lg">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="badge mb-2" style="background: rgba(45, 212, 191, 0.1); color: var(--cyan); border: 1px solid var(--border);">SUPPORT HUB</span>
                        <h2 class="fw-900 m-0" style="font-family: 'Outfit';">Resolve Ticket <span class="text-cyan">#<?php echo $msg_id; ?></span></h2>
                    </div>
                    <a href="messages.php" class="btn btn-outline-light btn-sm rounded-pill px-4">Exit Terminal</a>
                </div>

                <div class="original-msg mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small opacity-50 fw-bold text-uppercase" style="letter-spacing: 1px;">Incoming Transmission</div>
                        <span class="badge bg-black text-cyan border border-info" style="font-size: 0.7rem; border-color: var(--border) !important;">PATIENT INQUIRY</span>
                    </div>
                    <p class="mb-0 fs-5" style="font-style: italic; opacity: 0.9; line-height: 1.6;">"<?php echo nl2br(htmlspecialchars($msg['message'])); ?>"</p>
                    
                    <hr class="my-3 opacity-10">
                    
                    <div class="d-flex align-items-center small text-cyan fw-bold">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-person-circle me-2 fs-5"></i> 
                            <?php echo htmlspecialchars($msg['username'] ?? 'Anonymous Entity'); ?>
                        </div>
                        <span class="text-white opacity-25 mx-3">|</span> 
                        <div class="text-white opacity-50">
                            <i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($msg['email'] ?? 'No Route'); ?>
                        </div>
                    </div>
                </div>

                <form action="reply_message.php?id=<?php echo $msg_id; ?>" method="POST">
                    <input type="hidden" name="message_id" value="<?php echo $msg_id; ?>">
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-cyan text-uppercase" style="letter-spacing: 1px;">Official Admin Response</label>
                        <textarea name="admin_reply" class="form-control" rows="6" placeholder="Construct your resolution message..." required></textarea>
                    </div>

                    <button type="submit" name="submit_reply" class="btn btn-cyan w-100 py-3 shadow">
                        <i class="bi bi-shield-check me-2"></i>AUTHORIZE & SEND RESPONSE
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>