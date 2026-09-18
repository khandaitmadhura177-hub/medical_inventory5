<?php
session_start();
include('../config/db_connect.php');

if(!isset($_SESSION['role']) || $_SESSION['role'] != 1) { exit("Access Denied"); }

if(isset($_GET['id']) && isset($_GET['type'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $type = $_GET['type']; // 'support' or 'contact'

    // Determine which table to purge
    $table = ($type === 'support') ? 'support_messages' : 'contact_messages';
    
    $del_query = "DELETE FROM $table WHERE id = '$id'";
    
    if(mysqli_query($conn, $del_query)) {
        $loc = ($type === 'support') ? 'messages.php' : 'delete_message.php';
        header("Location: $loc?msg=Transmission purged successfully");
    }
}
?>