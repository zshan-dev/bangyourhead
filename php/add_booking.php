/**
 * Booking Creation Handler
 * 
 * @author Seif Otefa (400557672) * 
*  @date 2024-04-25
 * 
 * Handles creation of new service bookings:
 * - Validates user authentication
 * - Checks for booking time conflicts
 * - Creates new booking records
 * - Returns JSON response with booking status
 */

<?php
session_start();
require_once 'connect.php';

/**
 * Creates a new service booking
 * 
 * Process:
 * 1. Validates user session
 * 2. Parses and validates input data
 * 3. Checks for conflicting bookings
 * 4. Creates new booking record
 * 
 * Expected JSON input:
 * {
 *   "service_name": string,
 *   "date": "YYYY-MM-DD",
 *   "time": "HH:MM:SS"
 * }
 * 
 * @return JSON Response with format:
 * {
 *   "success": boolean,
 *   "booking_id"?: number,
 *   "message"?: string,
 *   "error"?: string
 * }
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Log the incoming request
error_log("Received booking request");
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    error_log("User not logged in");
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// Get POST data
$rawData = file_get_contents('php://input');
error_log("Raw POST data: " . $rawData);

$data = json_decode($rawData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error: " . json_last_error_msg());
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data: ' . json_last_error_msg()]);
    exit();
}

error_log("Decoded data: " . print_r($data, true));

if (!isset($data['service_name']) || !isset($data['date'])) {
    error_log("Missing required fields");
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

try {
    // Check for conflicting bookings (within 2 hours before or after)
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM bookings 
                          WHERE booking_date = ? 
                          AND (
                              (start_time BETWEEN DATE_SUB(?, INTERVAL 2 HOUR) AND DATE_ADD(?, INTERVAL 2 HOUR))
                              OR (end_time BETWEEN DATE_SUB(?, INTERVAL 2 HOUR) AND DATE_ADD(?, INTERVAL 2 HOUR))
                          )");
    
    $start_time = $data['time'];
    $stmt->execute([
        $data['date'],
        $start_time,
        $start_time,
        $start_time,
        $start_time
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'There is already a booking within 2 hours of this time. Please choose a different time.'
        ]);
        exit();
    }

    // Calculate end time (2 hours after start time)
    $end_time = date('H:i:s', strtotime($start_time . ' +2 hour'));
    
    // Insert booking into database
    $stmt = $dbh->prepare("INSERT INTO bookings (user_email, service_name, booking_date, start_time, end_time) 
                          VALUES (?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $_SESSION['email'],
        $data['service_name'], 
        $data['date'],
        $start_time,
        $end_time
    ]);
    
    // Get the ID of the newly created booking
    $bookingId = $dbh->lastInsertId();
    error_log("Created booking with ID: " . $bookingId);
    
    echo json_encode([
        'success' => true,
        'booking_id' => $bookingId,
        'message' => 'Booking created successfully'
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'details' => $e->getMessage()
    ]);
} 