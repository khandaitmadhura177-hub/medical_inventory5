<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// DATA QUERY: Fetch all general contact messages
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$total_msgs = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MIMS | Contact Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Outfit:wght@700;900&display=swap');
        :root { --cyan: #2DD4BF; --bg: #030712; --card-bg: #0b0e14; --border: rgba(45, 212, 191, 0.12); }
        body { background: var(--bg); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; }
        .comm-header { background: var(--card-bg); border-radius: 28px; padding: 40px; margin-top: 30px; border: 1px solid var(--border); }
        .msg-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 20px; padding: 25px; margin-bottom: 20px; transition: 0.3s; }
        .msg-card:hover { border-color: var(--cyan); transform: translateX(10px); background: #000; }
        .msg-icon { width: 50px; height: 50px; background: rgba(45, 212, 191, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--cyan); font-size: 1.5rem; margin-right: 15px; }
        .badge-count { background: var(--cyan); color: #030712; font-weight: 800; border-radius: 50px; padding: 4px 15px; font-size: 0.65rem; }
        .status-alert { background: rgba(45, 212, 191, 0.1); color: var(--cyan); border: 1px solid var(--border); border-radius: 50px; font-weight: 600; }
    </style>
</head>
<body>

<div class="container pb-5">
    <div class="comm-header mb-4 d-flex justify-content-between align-items-center">
        <div>
            <span class="badge-count mb-2 d-inline-block">PUBLIC INBOX / SYNCED</span>
            <h1 style="font-family: 'Outfit'; font-weight: 900;" class="m-0">Contact Inquiries <span style="color: var(--cyan)">(<?php echo $total_msgs; ?>)</span></h1>
        </div>
        <a href="dashboard.php" class="btn btn-outline-info rounded-pill px-4 fw-bold" style="border-color: var(--cyan); color: var(--cyan);">
            <i class="bi bi-cpu me-2"></i>DASHBOARD
        </a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="alert status-alert px-4 py-2 mb-4 d-inline-block shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-9 mx-auto">
            <?php if($total_msgs > 0): ?>
                <?php while($msg = mysqli_fetch_assoc($result)): ?>
                    <div class="msg-card d-flex align-items-start shadow-sm">
                        <div class="msg-icon"><i class="bi bi-envelope-paper"></i></div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="fw-bold mb-0" style="color: var(--cyan)">
                                    <?php echo htmlspecialchars($msg['name'] ?? $msg['fullname'] ?? 'Secure Sender'); ?>
                                </h6>
                                <span class="small opacity-50"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <div class="small opacity-75 fw-bold mb-3"><i class="bi bi-at me-1"></i><?php echo htmlspecialchars($msg['email']); ?></div>
                            <p class="text-white opacity-75 mb-4" style="line-height: 1.7;">"<?php echo nl2br(htmlspecialchars($msg['message'])); ?>"</p>
                            
                            <div class="d-flex gap-2">
                                <a href="mailto:<?php echo $msg['email']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-4 fw-bold">Reply via Mail</a>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-4 fw-bold" onclick="confirmDelete(<?php echo $msg['id']; ?>)">Dismiss</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 opacity-50">
                    <i class="bi bi-mailbox2" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No public inquiries.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if(confirm('Permanently clear this inquiry?')) {
        // Points to the new logic script
        window.location.href = 'delete_logic.php?id=' + id + '&type=contact';
    }
}
</script>
</body>
</html>