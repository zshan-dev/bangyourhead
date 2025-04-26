/**
 * Booking Details Handler
 * 
 * @author Your Name (Your Student Number)
 * @date 2024-04-25
 * 
 * Retrieves detailed information about a specific booking:
 * - Validates user authentication
 * - Fetches booking and associated user details
 * - Determines user permissions
 * - Returns formatted booking information
 * 
 * Expected GET parameters:
 * - id: number (booking ID)
 * 
 * @return JSON Response with format:
 * {
 *   service_name: string,
 *   name: string,
 *   date: string,
 *   start_time: string,
 *   end_time: string,
 *   is_admin: boolean,
 *   can_cancel: boolean,
 *   email: string,
 *   address: string
 * }
 * 
 * HTTP Status Codes:
 * - 200: Success
 * - 400: Invalid booking ID
 * - 401: Not logged in
 * - 404: Booking not found
 * - 500: Database error
 */

<?php
session_start();
require_once 'connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Debug session info
error_log("Session info - Email: " . $_SESSION['email'] . ", Is Admin: " . (isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'not set'));

// Get and validate booking ID from query string
$booking_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking and associated user details
    $stmt = $dbh->prepare("SELECT bookings.*, users.first_name, users.last_name, users.email as booker_email,
                          users.address
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

    // Check user permissions
    $is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    $is_owner = $_SESSION['email'] === $booking['booker_email'];

    // Prepare response with booking details
    $response = [
        'service_name' => $booking['service_name'],
        'name' => $booking['first_name'] . ' ' . $booking['last_name'],
        'date' => $booking['booking_date'],
        'start_time' => $booking['start_time'],
        'end_time' => $booking['end_time'],
        'is_admin' => $is_admin,
        'can_cancel' => $is_admin || $is_owner,
        'email' => $booking['booker_email'],
        'address' => $booking['address']
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?> 