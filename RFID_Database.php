<?php
session_start();
include 'DB_Connection.php';

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

// Verify CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit();
    }

    // Retrieve and sanitize form data
    $rfid = filter_var($_POST['rfid'] ?? null, FILTER_SANITIZE_STRING);

    // Validate form data
    if (empty($rfid)) {
        echo json_encode(['error' => 'RFID is required']);
        exit();
    }

    try {
        // Insert RFID data into the database
        $sql = 'INSERT INTO RFID_Logs (user_id, rfid, timestamp) VALUES (:user_id, :rfid, NOW())';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $_SESSION['id']);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->execute();

        echo json_encode(['success' => 'RFID logged successfully']);
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        echo json_encode(['error' => 'Database error']);
    }
}

// Auto-logout logic
if (isset($_POST['auto_logout']) && $_POST['auto_logout'] === 'true') {
    session_destroy();
    echo json_encode(['success' => 'User logged out automatically']);
    exit();
}

$conn = null;
?>