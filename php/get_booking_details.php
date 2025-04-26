<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

$booking_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$booking_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid booking ID']);
    exit();
}

try {
    // Get booking details
    $stmt = $dbh->prepare("SELECT bookings.*, users.first_name, users.last_name, users.email as booker_email 
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

    // Prepare response based on user role and ownership
    $response = [
        'service_name' => $booking['service_name'],
        'name' => $booking['first_name'] . ' ' . $booking['last_name'],
        'time' => $booking['time']
    ];

    // If user is admin or booking owner, add sensitive details
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 || 
        $_SESSION['email'] === $booking['booker_email']) {
        $response['address'] = $booking['address'];
        $response['phone'] = $booking['phone'];
        $response['can_cancel'] = true;
    } else {
        $response['can_cancel'] = false;
    }

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?> 