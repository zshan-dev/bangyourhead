/**
 * Logout Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Handles user logout by destroying the current session
 * and returning a JSON response confirming successful logout.
 */

<?php
session_start();
session_destroy();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?> 