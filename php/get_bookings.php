/**
 * Booking Retrieval Handler
 * 
 * @author Seif Otefa (400557672)
 * @date 2024-04-25
 * 
 * Retrieves and formats service bookings for calendar display:
 * - Validates user authentication
 * - Fetches all bookings from database
 * - Formats data for FullCalendar integration
 * - Handles booking ownership and admin privileges
 * 
 * @return JSON Array of events in FullCalendar format:
 * [{
 *   id: number,
 *   title: string,
 *   start: string (ISO8601),
 *   end: string (ISO8601),
 *   extendedProps: {
 *     name: string,
 *     canEdit: boolean
 *   },
 *   color: string
 * }]
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

try {
    // Get all bookings with user details
    $stmt = $dbh->prepare("SELECT bookings.*, users.first_name, users.last_name 
                          FROM bookings 
                          JOIN users ON bookings.user_email = users.email");
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format bookings for FullCalendar
    $events = array_map(function($booking) {
        $isOwner = $_SESSION['email'] === $booking['user_email'];
        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
        
        return [
            'id' => $booking['id'],
            'title' => $booking['service_name'],
            'start' => $booking['booking_date'] . 'T' . $booking['start_time'],
            'end' => $booking['booking_date'] . 'T' . $booking['end_time'],
            'extendedProps' => [
                'name' => $booking['first_name'] . ' ' . $booking['last_name'],
                'canEdit' => $isOwner || $isAdmin
            ],
            'color' => $isOwner ? '#3788d8' : '#666'  // Blue for own bookings, gray for others
        ];
    }, $bookings);

    echo json_encode($events);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
} 