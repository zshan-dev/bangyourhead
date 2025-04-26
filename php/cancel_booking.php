<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Get booking ID from POST data
$data = json_decode(file_get_contents('php/input'), true);
$booking_id = $data['booking_id'] ?? null;

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking details to check permissions
    $stmt = $dbh->prepare("SELECT user_id, users.email as booker_email 
                          FROM bookings 
                          JOIN users ON bookings.user_id = users.id 
                          WHERE bookings.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found']);
        exit();
    }

    // Check if user has permission to cancel
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        if ($_SESSION['email'] !== $booking['booker_email']) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authorized to cancel this booking']);
            exit();
        }
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