<?php
session_start();
include('../config/db_connect.php');

/**
 * MIMS Theme Synchronizer
 * Persists visual preferences across the medical terminal.
 */

if(isset($_SESSION['user_id']) && isset($_GET['theme'])) {
    
    $user_id = $_SESSION['user_id'];
    $theme = mysqli_real_escape_string($conn, $_GET['theme']);

    // 1. Logic: Allow only valid themes (whitelist)
    $allowed_themes = ['dark', 'light', 'neural'];
    if(!in_array($theme, $allowed_themes)) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid theme profile.']);
        exit;
    }

    // 2. Logic: Update database
    $query = "UPDATE users SET theme = '$theme' WHERE id = '$user_id'";
    
    if(mysqli_query($conn, $query)) {
        // 3. Logic: Sync session so other pages know the new theme immediately
        $_SESSION['user_theme'] = $theme;

        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'theme' => $theme]);
        http_response_code(200);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database failure.']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing data.']);
}
?>