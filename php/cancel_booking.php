/**
 * Booking Cancellation Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Handles service booking cancellation:
 * - Validates user authentication
 * - Verifies booking ownership/admin rights
 * - Removes booking from database
 * 
 * Expected JSON input:
 * {
 *   booking_id: number
 * }
 * 
 * @return JSON Response with format:
 * {
 *   success?: boolean,
 *   error?: string
 * }
 * 
 * HTTP Status Codes:
 * - 200: Success
 * - 400: Invalid booking ID
 * - 401: Not logged in
 * - 403: Not authorized
 * - 404: Booking not found
 * - 500: Database error
 */

<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Get and validate booking ID from POST data
$data = json_decode(file_get_contents('php://input'), true);
$booking_id = $data['booking_id'] ?? null;

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking details to check permissions
    $stmt = $dbh->prepare("SELECT bookings.*, users.email as booker_email
                          FROM bookings
                          JOIN users ON bookings.user_email = users.email
                          WHERE bookings.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        exit();
    }

    // Check if user has permission to cancel
    $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    $is_owner = $_SESSION['email'] === $booking['booker_email'];
    
    if (!$is_admin && !$is_owner) {
        http_response_code(403);
        echo json_encode(['error' => 'Not authorized to cancel this booking']);
        exit();
    }

    // Delete the booking
    $stmt = $dbh->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?> 