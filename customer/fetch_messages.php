<?php
session_start();
include('../config/db_connect.php');
header('Content-Type: application/json');

// Security Check: Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch messages and the admin's reply linked to this specific patient
// We use ORDER BY id ASC to keep the conversation in chronological order
$query = "SELECT message, admin_reply, created_at 
          FROM support_messages 
          WHERE user_id = '$user_id' 
          ORDER BY id ASC";

$res = mysqli_query($conn, $query);

$messages = [];
if ($res) {
    while($row = mysqli_fetch_assoc($res)) { 
        $messages[] = $row; 
    }
}

// Return the conversation array to the dashboard's JavaScript
echo json_encode($messages);